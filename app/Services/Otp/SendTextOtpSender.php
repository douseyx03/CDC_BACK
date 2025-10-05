<?php

namespace App\Services\Otp;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendTextOtpSender implements OtpSender
{
    public function send(string $phoneNumber, string $code, array $context = []): void
    {
        $config = config('services.sendtext', []);
        $apiKey = $config['api_key'] ?? null;
        $apiSecret = $config['api_secret'] ?? null;

        if (empty($apiKey) || empty($apiSecret)) {
            throw new OtpDeliveryException('Clés API SendText manquantes.');
        }

        $baseUrl = rtrim($config['base_url'] ?? 'https://api.sendtext.sn', '/');
        $endpoint = $config['otp_endpoint'] ?? '/v1/sms';
        $timeout = (int) ($config['timeout'] ?? 10);
        $url = $baseUrl.$endpoint;

        $senderName = $config['sender_name'] ?? null;
        $messageTemplate = $config['otp_template'] ?? 'Votre code OTP est : :code';
        $messageType = $config['message_type'] ?? 'normal';

        $name = $this->resolveRecipientName($context);
        $replacements = array_filter([
            ':code' => $code,
            ':name' => $name,
        ], static fn ($value) => $value !== null);

        $message = strtr($messageTemplate, $replacements);

        $payload = array_filter([
            'sender_name' => $senderName,
            'sms_type' => $messageType,
            'phone' => $this->formatPhoneNumber($phoneNumber),
            'text' => $message,
        ], static fn ($value) => $value !== null && $value !== '');

        try {
            $response = Http::withHeaders([
                'SNT-API-KEY' => $apiKey,
                'SNT-API-SECRET' => $apiSecret,
            ])->acceptJson()
                ->asJson()
                ->timeout($timeout)
                ->post($url, $payload);
        } catch (Throwable $th) {
            throw new OtpDeliveryException(
                'Impossible de contacter le service SMS SendText.',
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

            Log::warning('SendText OTP delivery failed.', [
                'status' => $response->status(),
                'response' => $body,
            ]);

            throw new OtpDeliveryException(
                'SendText a retourné une erreur lors de l\'envoi du code OTP.',
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

    private function formatPhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber);

        return $digits !== null && $digits !== '' ? $digits : $phoneNumber;
    }
}
