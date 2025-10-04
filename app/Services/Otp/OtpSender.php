<?php

namespace App\Services\Otp;

interface OtpSender
{
    /**
     * Send an OTP to the given phone number.
     *
     * @param  string  $phoneNumber  Recipient phone number in international format.
     * @param  string  $code  One-time password plain value.
     * @param  array<string, mixed>  $context  Extra context (e.g. user name) for templating.
     */
    public function send(string $phoneNumber, string $code, array $context = []): void;
}
