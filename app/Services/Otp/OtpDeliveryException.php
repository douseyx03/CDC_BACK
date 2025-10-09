<?php

namespace App\Services\Otp;

use RuntimeException;
use Throwable;

class OtpDeliveryException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly mixed $payload = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status ?? 0, $previous);
    }
}
