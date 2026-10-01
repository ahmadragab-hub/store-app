<?php

namespace App\Contracts;

final class PaymentChargeResult
{
    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PROCESSING = 'processing';

    public function __construct(
        public readonly bool $succeeded,
        public readonly string $status,
        public readonly ?string $externalPaymentId = null,
        public readonly ?string $failureMessage = null,
    ) {
    }

    public static function succeeded(string $externalPaymentId): self
    {
        return new self(true, self::STATUS_SUCCEEDED, $externalPaymentId);
    }

    public static function failed(?string $externalPaymentId = null, ?string $message = null): self
    {
        return new self(false, self::STATUS_FAILED, $externalPaymentId, $message);
    }

    public static function processing(string $externalPaymentId): self
    {
        return new self(false, self::STATUS_PROCESSING, $externalPaymentId);
    }
}
