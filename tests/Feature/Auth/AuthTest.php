<?php

namespace Tests\Feature\Auth;

use App\Models\PhoneVerificationCode;
use App\Models\User;
use App\Services\Otp\OtpDeliveryException;
use App\Services\Otp\OtpSender;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_verification_requirements(): void
    {
        Notification::fake();
        Http::fake();

        $response = $this->postJson('/api/auth/register', [
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => 'john.doe@example.com',
            'telephone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJson([
                'requires_email_verification' => true,
                'requires_phone_verification' => true,
            ]);

        $user = User::first();

        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertArrayNotHasKey('otp_preview', $response->json());
        $this->assertNull($user->phoneVerificationCode);
        Http::assertNothingSent();
    }

    public function test_user_cannot_login_until_email_verified(): void
    {
        Notification::fake();
        Http::fake();

        $user = User::factory()->unverified()->phoneUnverified()->create([
            'telephone' => '+1234567891',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertForbidden();
        Notification::assertSentTo($user, VerifyEmail::class);
        Http::assertNothingSent();
    }

    public function test_user_can_login_and_receive_token_when_verified(): void
    {
        Http::fake();

        $user = User::factory()->create([
            'telephone' => '+1234567892',
            'phone_verified_at' => now(),
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'requires_phone_verification',
                'user' => ['id', 'nom', 'prenom', 'email', 'telephone', 'email_verified', 'phone_verified'],
            ])
            ->assertJson([
                'requires_phone_verification' => false,
            ]);

        $this->assertTrue($response->json('user.phone_verified'));
        Http::assertNothingSent();
    }

    public function test_user_must_verify_phone_before_token_is_issued(): void
    {
        Http::fake([
            rtrim(config('services.axiomtext.base_url'), '/').'/*' => Http::response(['status' => 'ok']),
        ]);

        $user = User::factory()->create([
            'telephone' => '+1234567892',
            'phone_verified_at' => null,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(202)
            ->assertJson([
                'requires_phone_verification' => true,
            ]);

        $this->assertNull($response->json('token'));
        $this->assertNotNull($user->fresh()->phoneVerificationCode);

        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($user) {
            $expectedUrl = rtrim(config('services.axiomtext.base_url'), '/').config('services.axiomtext.otp_endpoint');
            $recipient = $request['recipient'] ?? $request['phone'] ?? null;
            $message = $request['message'] ?? '';

            return $request->url() === $expectedUrl
                && $recipient === $user->telephone
                && is_string($message)
                && str_contains($message, 'Votre code OTP');
        });
    }


    public function test_login_returns_error_if_otp_delivery_fails(): void
    {
        $originalSender = app(OtpSender::class);

        app()->instance(OtpSender::class, new class implements OtpSender {
            public function send(string $phoneNumber, string $code, array $context = []): void
            {
                throw new OtpDeliveryException('Service indisponible');
            }
        });

        Http::fake();

        $user = User::factory()->create([
            'telephone' => '+1234567895',
            'phone_verified_at' => null,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(502)
            ->assertJson([
                'message' => "Impossible d'envoyer le code OTP. Veuillez réessayer plus tard.",
            ]);

        $this->assertNull($response->json('token'));
        $this->assertNull($user->fresh()->phoneVerificationCode);

        app()->instance(OtpSender::class, $originalSender);
    }
    public function test_user_can_verify_phone_number_with_valid_code(): void
    {
        Http::fake();

        $user = User::factory()->create([
            'telephone' => '+1234567893',
            'phone_verified_at' => null,
            'password' => bcrypt('password123'),
        ]);

        $code = '123456';
        PhoneVerificationCode::create([
            'user_id' => $user->id,
            'code' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(2),
        ]);

        $response = $this->postJson('/api/auth/phone/verify', [
            'telephone' => $user->telephone,
            'code' => $code,
            'device_name' => 'iphone',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'nom', 'prenom', 'email', 'telephone', 'email_verified', 'phone_verified'],
            ])
            ->assertJsonFragment(['token_type' => 'Bearer']);

        $this->assertNotNull($response->json('token'));
        $this->assertNotNull($user->fresh()->phone_verified_at);
        $this->assertDatabaseMissing('phone_verification_codes', [
            'user_id' => $user->id,
        ]);
        Http::assertNothingSent();
    }

    public function test_user_can_request_new_phone_otp(): void
    {
        Http::fake([
            rtrim(config('services.axiomtext.base_url'), '/').'/*' => Http::response(['status' => 'ok']),
        ]);

        $user = User::factory()->create([
            'telephone' => '+1234567894',
            'phone_verified_at' => null,
            'password' => bcrypt('password123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/phone/otp');

        $response->assertOk();
        $this->assertNotNull($user->fresh()->phoneVerificationCode);

        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($user) {
            $expectedUrl = rtrim(config('services.axiomtext.base_url'), '/').config('services.axiomtext.otp_endpoint');
            $recipient = $request['recipient'] ?? $request['phone'] ?? null;

            return $request->url() === $expectedUrl
                && $recipient === $user->telephone;
        });
    }
}
