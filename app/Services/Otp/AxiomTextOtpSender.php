<?php

namespace App\Services\Otp;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AxiomTextOtpSender implements OtpSender
{
    public function send(string $phoneNumber, string $code, array $context = []): void
    {
        $config = config('services.axiomtext', []);
        $apiKey = $config['api_key'] ?? null;

        if (empty($apiKey)) {
            throw new OtpDeliveryException('Clé API AxiomText manquante.');
        }

        $baseUrl = rtrim($config['base_url'] ?? 'https://api.axiomtext.com', '/');
        $endpoint = $config['otp_endpoint'] ?? '/api/sms/otp/send';
        $timeout = (int) ($config['timeout'] ?? 10);
        $url = $baseUrl.$endpoint;

        $senderId = $config['sender_id'] ?? 'OTP';
        $messageTemplate = $config['otp_template'] ?? 'Votre code OTP est : :code';

        $name = $this->resolveRecipientName($context);
        $replacements = array_filter([
            ':code' => $code,
            ':name' => $name,
        ], static fn ($value) => $value !== null);

        $message = strtr($messageTemplate, $replacements);

        $payload = array_filter([
            'sender_id' => $senderId,
            'sender' => $senderId,
            'recipient' => $phoneNumber,
            'phone' => $phoneNumber,
            'message' => $message,
            'type' => 'OTP',
        ], static fn ($value) => $value !== null && $value !== '');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Accept' => 'application/json',
            ])->timeout($timeout)->post($url, $payload);
        } catch (Throwable $th) {
            throw new OtpDeliveryException(
                'Impossible de contacter le service SMS AxiomText.',
                null,
                null,
                $th,
            );
        }

        if ($response->failed()) {
            $body = $response->json();
            if ($body === null) {
                $body = $response->body();
            }

            Log::warning('AxiomText OTP delivery failed.', [
                'status' => $response->status(),
                'response' => $body,
            ]);

            throw new OtpDeliveryException(
                'AxiomText a retourné une erreur lors de l\'envoi du code OTP.',
                $response->status(),
                $body,
            );
        }
    }

    private function resolveRecipientName(array $context): ?string
    {
        if (!empty($context['name']) && is_string($context['name'])) {
            return $context['name'];
        }

        $user = Arr::get($context, 'user');
        if ($user instanceof User) {
            $fullname = trim(sprintf('%s %s', $user->prenom ?? '', $user->nom ?? ''));

            return $fullname !== '' ? $fullname : null;
        }

        return null;
    }
}
