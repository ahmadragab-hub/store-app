<?php

namespace App\Services;

use App\Contracts\PaymentChargeResult;
use App\Contracts\PaymentGateway;
use App\Exceptions\StoreException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGateway
{
    public function __construct(
        private ?string $secretKey,
    ) {
    }

    public function charge(string $paymentToken, float $amount, array $context = []): PaymentChargeResult
    {
        if (! is_string($this->secretKey) || $this->secretKey === '') {
            throw new StoreException('Stripe is not configured. Set STRIPE_SECRET or use PAYMENT_DRIVER=mock.');
        }

        if ($amount <= 0 || $paymentToken === '') {
            return PaymentChargeResult::failed(message: 'Invalid payment request.');
        }

        $orderId = isset($context['order_id']) ? (int) $context['order_id'] : 0;
        $idempotencyKey = (string) ($context['idempotency_key'] ?? '');

        if ($orderId < 1 || $idempotencyKey === '') {
            throw new StoreException('Missing payment context for Stripe.');
        }

        try {
            $stripe = new StripeClient($this->secretKey);

            $requestOptions = $idempotencyKey !== ''
                ? ['idempotency_key' => $idempotencyKey]
                : [];

            $intent = $stripe->paymentIntents->create([
                'amount' => (int) round($amount * 100),
                'currency' => strtolower((string) config('payments.currency', 'usd')),
                'payment_method' => $paymentToken,
                'confirm' => true,
                'metadata' => [
                    'order_id' => (string) $orderId,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ], $requestOptions);

            return $this->resultFromIntent($intent);
        } catch (ApiErrorException $e) {
            report($e);

            return PaymentChargeResult::failed(message: 'Stripe payment failed.');
        } catch (\Throwable $e) {
            report($e);

            throw new StoreException('Stripe payment failed.');
        }
    }

    public function driver(): string
    {
        return 'stripe';
    }

    private function failureMessageFromIntent(object $intent): string
    {
        $lastError = $intent->last_payment_error ?? null;

        if (is_object($lastError) && isset($lastError->message) && is_string($lastError->message)) {
            return $lastError->message;
        }

        return 'Payment was not completed.';
    }

    /**
     * @param  \Stripe\PaymentIntent|object  $intent
     */
    public function resultFromIntent(object $intent): PaymentChargeResult
    {
        $status = (string) ($intent->status ?? '');
        $id = isset($intent->id) ? (string) $intent->id : null;

        return match ($status) {
            'succeeded' => PaymentChargeResult::succeeded((string) $id),
            'processing', 'requires_action', 'requires_confirmation' => PaymentChargeResult::processing((string) $id),
            default => PaymentChargeResult::failed(
                $id,
                $this->failureMessageFromIntent($intent),
            ),
        };
    }
}
