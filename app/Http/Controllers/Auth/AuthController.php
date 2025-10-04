<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyPhoneRequest;
use App\Models\User;
use App\Services\Otp\OtpSender;
use App\Services\Otp\OtpDeliveryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly OtpSender $otpSender)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $user = User::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'telephone' => $data['telephone'],
                'password' => Hash::make($data['password']),
            ]);

            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Utilisateur enregistré. Consultez votre e-mail pour valider votre mot de passe via le lien magique.',
                'requires_email_verification' => !$user->hasVerifiedEmail(),
                'requires_phone_verification' => $user->phone_verified_at === null,
                'verification_url_preview' => app()->isLocal() ? $this->verificationUrl($user) : null,
            ], 201);
        } catch (Throwable $th) {
            return response()->json([
                'message' => "Erreur lors de l'enregistrement de l'utilisateur.",
                'error' => app()->isLocal() ? $th->getMessage() : null,
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();

            $user = null;
            if (!empty($credentials['email'])) {
                $user = User::where('email', $credentials['email'])->first();
            } elseif (!empty($credentials['telephone'])) {
                $user = User::where('telephone', $credentials['telephone'])->first();
            }

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'message' => 'Identifiants invalides.',
                ], 422);
            }

            if (!$user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();

                return response()->json([
                    'message' => 'Adresse e-mail non vérifiée. Un nouveau lien de vérification a été envoyé.',
                    'verification_url_preview' => app()->isLocal() ? $this->verificationUrl($user) : null,
                ], 403);
            }

            // if ($user->phone_verified_at === null) {
            //     try {
            //         $otp = $this->generatePhoneVerificationCode($user);
            //     } catch (OtpDeliveryException $exception) {
            //         return response()->json([
            //             'message' => "Impossible d'envoyer le code OTP. Veuillez réessayer plus tard.",
            //             'error' => app()->isLocal() ? $exception->getMessage() : null,
            //         ], 502);
            //     }

            //     return response()->json([
            //         'message' => 'Code OTP envoyé. Veuillez vérifier votre téléphone pour finaliser la connexion.',
            //         'requires_phone_verification' => true,
            //         'user' => [
            //             'email' => $user->email,
            //             'telephone' => $user->telephone,
            //         ],
            //         'otp_preview' => app()->isLocal() ? $otp : null,
            //     ], 202);
            // }

            $token = $user->createToken($credentials['device_name'] ?? 'api-token');

            return response()->json([
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'requires_phone_verification' => false,
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'telephone' => $user->telephone,
                    'email_verified' => $user->hasVerifiedEmail(),
                    'phone_verified' => true,
                ],
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'message' => 'Erreur lors de la connexion.',
                'error' => app()->isLocal() ? $th->getMessage() : null,
            ], 500);
        }
    }

    public function requestPhoneOtp(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            if ($user->phone_verified_at !== null) {
                return response()->json([
                    'message' => 'Le numéro de téléphone est déjà vérifié.',
                ]);
            }

            try {
                $otp = $this->generatePhoneVerificationCode($user);
            } catch (OtpDeliveryException $exception) {
                return response()->json([
                    'message' => "Impossible d'envoyer le code OTP. Veuillez réessayer plus tard.",
                    'error' => app()->isLocal() ? $exception->getMessage() : null,
                ], 502);
            }

            return response()->json([
                'message' => 'Un nouveau code OTP a été généré.',
                'otp_preview' => app()->isLocal() ? $otp : null,
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'message' => 'Erreur lors de la génération du code OTP.',
                'error' => app()->isLocal() ? $th->getMessage() : null,
            ], 500);
        }
    }

    public function verifyPhone(VerifyPhoneRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            /** @var User|null $user */
            $user = $request->user();

            if (!$user) {
                $query = User::query();

                if (!empty($validated['email'])) {
                    $query->where('email', $validated['email']);
                } elseif (!empty($validated['telephone'])) {
                    $query->where('telephone', $validated['telephone']);
                }

                $user = $query->first();

                if (!$user) {
                    return response()->json([
                        'message' => 'Utilisateur introuvable.',
                    ], 404);
                }
            }

            $codeEntry = $user->phoneVerificationCode;

            $plainToken = null;
            $tokenType = null;

            if ($user->phone_verified_at !== null) {
                if (!$request->user()) {
                    $token = $user->createToken($validated['device_name'] ?? 'api-token');
                    $plainToken = $token->plainTextToken;
                    $tokenType = 'Bearer';
                }

                return response()->json(array_filter([
                    'message' => 'Le numéro de téléphone est déjà vérifié.',
                    'token' => $plainToken,
                    'token_type' => $tokenType,
                    'user' => [
                        'id' => $user->id,
                        'nom' => $user->nom,
                        'prenom' => $user->prenom,
                        'email' => $user->email,
                        'telephone' => $user->telephone,
                        'email_verified' => $user->hasVerifiedEmail(),
                        'phone_verified' => true,
                    ],
                ], static fn ($value) => $value !== null));
            }

            if (!$codeEntry) {
                return response()->json([
                    'message' => 'Aucun code OTP actif. Veuillez en demander un nouveau.',
                ], 404);
            }

            if ($codeEntry->expires_at->isPast()) {
                $codeEntry->delete();

                return response()->json([
                    'message' => 'Le code OTP a expiré. Veuillez en demander un nouveau.',
                ], 422);
            }

            if ($codeEntry->attempts >= 5) {
                $codeEntry->delete();

                try {
                    $otp = $this->generatePhoneVerificationCode($user);
                } catch (OtpDeliveryException $exception) {
                    return response()->json([
                        'message' => "Impossible d'envoyer le code OTP. Veuillez réessayer plus tard.",
                        'error' => app()->isLocal() ? $exception->getMessage() : null,
                    ], 502);
                }

                return response()->json([
                    'message' => 'Nombre de tentatives dépassé. Un nouveau code a été généré.',
                    'otp_preview' => app()->isLocal() ? $otp : null,
                ], 429);
            }

            if (!hash_equals($codeEntry->code, $this->hashCode($validated['code']))) {
                $codeEntry->increment('attempts');

                return response()->json([
                    'message' => 'Code OTP invalide.',
                ], 422);
            }

            $user->forceFill([
                'phone_verified_at' => now(),
            ])->save();

            $codeEntry->delete();

            if (!$request->user()) {
                $token = $user->createToken($validated['device_name'] ?? 'api-token');
                $plainToken = $token->plainTextToken;
                $tokenType = 'Bearer';
            }

            return response()->json(array_filter([
                'message' => 'Numéro de téléphone vérifié avec succès.',
                'token' => $plainToken,
                'token_type' => $tokenType,
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'telephone' => $user->telephone,
                    'email_verified' => $user->hasVerifiedEmail(),
                    'phone_verified' => true,
                ],
            ], static fn ($value) => $value !== null));
        } catch (Throwable $th) {
            return response()->json([
                'message' => 'Erreur lors de la vérification du numéro de téléphone.',
                'error' => app()->isLocal() ? $th->getMessage() : null,
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();
            $token = $user?->currentAccessToken();

            if ($token) {
                $token->delete();
            }

            return response()->json([
                'message' => 'Déconnexion effectuée.',
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'message' => 'Erreur lors de la déconnexion.',
                'error' => app()->isLocal() ? $th->getMessage() : null,
            ], 500);
        }
    }

    private function generatePhoneVerificationCode(User $user): string
    {
        $plain = Str::padLeft((string) random_int(0, 999999), 6, '0');
        $hashed = $this->hashCode($plain);
        $expiresAt = now()->addMinutes(2);

        $entry = $user->phoneVerificationCode()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'code' => $hashed,
                'expires_at' => $expiresAt,
                'attempts' => 0,
            ]
        );

        try {
            $this->otpSender->send($user->telephone, $plain, [
                'user' => $user,
            ]);
        } catch (Throwable $th) {
            $entry->delete();

            throw $th;
        }

        return $plain;
    }

    private function hashCode(string $code): string
    {
        return hash('sha256', $code);
    }

    private function verificationUrl(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }
}
