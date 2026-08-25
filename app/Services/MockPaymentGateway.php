<?php

namespace App\Services;

class MockPaymentGateway
{
    public const TEST_CARD = '4242424242424242';

    public function charge(string $cardNumber, float $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }

        $digits = preg_replace('/\D+/', '', $cardNumber);

        return $digits === self::TEST_CARD;
    }
}
