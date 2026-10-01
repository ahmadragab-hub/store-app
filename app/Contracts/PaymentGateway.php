<?php

namespace App\Contracts;

interface PaymentGateway
{
    /**
     * Charge an order. $paymentToken is gateway-specific
     * (mock: test card digits; Stripe: PaymentMethod id).
     *
     * @param  array{order_id?: int, idempotency_key?: string}  $context
     */
    public function charge(string $paymentToken, float $amount, array $context = []): PaymentChargeResult;

    public function driver(): string;
}
