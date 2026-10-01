<?php

namespace App\Services;

use App\Contracts\PaymentChargeResult;
use App\Contracts\PaymentGateway;

class MockPaymentGateway implements PaymentGateway
{
    public const TEST_CARD = '4242424242424242';

    public function charge(string $paymentToken, float $amount, array $context = []): PaymentChargeResult
    {
        if ($amount <= 0) {
            return PaymentChargeResult::failed(message: 'Invalid amount.');
        }

        $digits = preg_replace('/\D+/', '', $paymentToken) ?? '';

        $orderId = isset($context['order_id']) ? (int) $context['order_id'] : 0;
        $externalId = $orderId > 0 ? 'mock_pi_'.$orderId : 'mock_pi_unknown';

        if ($digits !== self::TEST_CARD) {
            return PaymentChargeResult::failed($externalId, 'Card was declined.');
        }

        return PaymentChargeResult::succeeded($externalId);
    }

    public function driver(): string
    {
        return 'mock';
    }
}
