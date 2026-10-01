<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentWebhookEvent;
use Illuminate\Support\Facades\DB;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookService
{
    public function __construct(
        private CheckoutService $checkout,
        private StripePaymentGateway $stripePayments,
    ) {
    }

    public function handle(string $payload, ?string $signatureHeader): void
    {
        $secret = (string) config('payments.stripe.webhook_secret', '');

        if ($secret === '') {
            abort(503, 'Stripe webhooks are not configured.');
        }

        if (! is_string($signatureHeader) || $signatureHeader === '') {
            abort(400, 'Missing Stripe signature.');
        }

        try {
            $event = Webhook::constructEvent($payload, $signatureHeader, $secret);
        } catch (SignatureVerificationException) {
            abort(400, 'Invalid Stripe signature.');
        } catch (\UnexpectedValueException) {
            abort(400, 'Invalid Stripe payload.');
        }

        DB::transaction(function () use ($event) {
            $recorded = PaymentWebhookEvent::query()->firstOrCreate(
                [
                    'provider' => 'stripe',
                    'event_id' => (string) $event->id,
                ],
                [
                    'event_type' => (string) $event->type,
                ],
            );

            if (! $recorded->wasRecentlyCreated) {
                return;
            }

            $this->dispatchEvent($event, $recorded);
        });
    }

    private function dispatchEvent(Event $event, PaymentWebhookEvent $record): void
    {
        $object = $event->data->object ?? null;

        if (! is_object($object) || ($object->object ?? null) !== 'payment_intent') {
            return;
        }

        $order = $this->resolveOrder($object);

        if (! $order) {
            return;
        }

        $record->update(['order_id' => $order->id]);

        if (! $this->paymentIntentMatchesOrder($object, $order)) {
            report(new \RuntimeException('Stripe PaymentIntent does not match order totals for order #'.$order->id));

            return;
        }

        $result = $this->stripePayments->resultFromIntent($object);

        match ($event->type) {
            'payment_intent.succeeded' => $this->checkout->applySuccessfulPayment(
                $order,
                (string) $object->id,
                'stripe',
            ),
            'payment_intent.payment_failed' => $this->checkout->recordFailedPayment(
                $order,
                (string) $object->id,
                $result->failureMessage,
            ),
            default => null,
        };
    }

    private function resolveOrder(object $intent): ?Order
    {
        $metadataOrderId = $this->metadataOrderId($intent);

        if ($metadataOrderId > 0) {
            $order = Order::query()->find($metadataOrderId);

            if ($order) {
                return $order;
            }
        }

        if (is_string($intent->id) && $intent->id !== '') {
            return Order::query()
                ->where('external_payment_id', $intent->id)
                ->first();
        }

        return null;
    }

    private function metadataOrderId(object $intent): int
    {
        $metadata = $intent->metadata ?? null;

        if (is_object($metadata) && isset($metadata->order_id)) {
            return (int) $metadata->order_id;
        }

        if (is_array($metadata) && isset($metadata['order_id'])) {
            return (int) $metadata['order_id'];
        }

        return 0;
    }

    private function paymentIntentMatchesOrder(object $intent, Order $order): bool
    {
        $expectedAmount = (int) round(((float) $order->total) * 100);
        $currency = strtolower((string) config('payments.currency', 'usd'));

        return (int) ($intent->amount ?? -1) === $expectedAmount
            && strtolower((string) ($intent->currency ?? '')) === $currency;
    }
}
