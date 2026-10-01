<?php

namespace Tests\Feature;

use App\Contracts\PaymentChargeResult;
use App\Mail\OrderPaidMail;
use App\Models\Order;
use App\Models\PaymentWebhookEvent;
use App\Models\Product;
use App\Models\User;
use App\Services\MockPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentArchitectureTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'whsec_test_secret_for_phpunit';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'payments.stripe.webhook_secret' => self::WEBHOOK_SECRET,
        ]);
    }

    public function test_mock_payment_stores_external_reference_and_marks_order_paid(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 5, 'price' => 12.50]);

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_name' => 'Buyer',
            'shipping_line1' => '1 Main St',
            'shipping_city' => 'Town',
            'shipping_postal' => '12345',
            'shipping_country' => 'US',
        ]);

        $order = Order::query()->firstOrFail();

        $this->actingAs($user)->post(route('checkout.pay.store', $order), [
            'card_number' => MockPaymentGateway::TEST_CARD,
        ])->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame(PaymentChargeResult::STATUS_SUCCEEDED, $order->payment_status);
        $this->assertSame('mock_pi_'.$order->id, $order->external_payment_id);
        $this->assertSame('pay_order_'.$order->id, $order->payment_idempotency_key);
        Mail::assertSent(OrderPaidMail::class, 1);
    }

    public function test_failed_mock_payment_keeps_order_pending_and_records_failure(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 3, 'price' => 10]);

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_name' => 'Buyer',
            'shipping_line1' => '1 Main St',
            'shipping_city' => 'Town',
            'shipping_postal' => '12345',
            'shipping_country' => 'US',
        ]);

        $order = Order::query()->firstOrFail();
        $originalKey = 'pay_order_'.$order->id;

        $this->actingAs($user)->post(route('checkout.pay.store', $order), [
            'card_number' => '4000000000000002',
        ])->assertSessionHas('error');

        $order->refresh();
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame(PaymentChargeResult::STATUS_FAILED, $order->payment_status);
        $this->assertNotNull($order->payment_failure_message);
        $this->assertNotSame($originalKey, $order->payment_idempotency_key);
    }

    public function test_duplicate_payment_attempt_is_rejected(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $product = Product::factory()->create(['stock' => 5, 'price' => 10]);

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_name' => 'Buyer',
            'shipping_line1' => '1 Main St',
            'shipping_city' => 'Town',
            'shipping_postal' => '12345',
            'shipping_country' => 'US',
        ]);

        $order = Order::query()->firstOrFail();

        $this->actingAs($user)->post(route('checkout.pay.store', $order), [
            'card_number' => MockPaymentGateway::TEST_CARD,
        ]);

        $this->actingAs($user)->post(route('checkout.pay.store', $order), [
            'card_number' => MockPaymentGateway::TEST_CARD,
        ])->assertNotFound();
    }

    public function test_stripe_webhook_rejects_invalid_signature(): void
    {
        $payload = $this->stripeEventPayload([
            'id' => 'pi_test_invalid_sig',
            'amount' => 1000,
            'currency' => 'usd',
            'status' => 'succeeded',
            'metadata' => ['order_id' => '1'],
        ], 'payment_intent.succeeded', 'evt_invalid_sig');

        $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            [
                'HTTP_Stripe-Signature' => 't='.time().',v1=invalid',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        )->assertStatus(400);
    }

    public function test_stripe_webhook_marks_order_paid_and_is_idempotent(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $order = $this->createOrder($user, [
            'total' => 10.00,
            'payment_driver' => 'stripe',
            'payment_status' => PaymentChargeResult::STATUS_PROCESSING,
            'external_payment_id' => 'pi_test_success',
        ]);

        $payload = $this->stripeEventPayload([
            'id' => 'pi_test_success',
            'amount' => 1000,
            'currency' => 'usd',
            'status' => 'succeeded',
            'metadata' => ['order_id' => (string) $order->id],
        ], 'payment_intent.succeeded', 'evt_success_1');

        $signature = $this->stripeSignature($payload, self::WEBHOOK_SECRET);

        $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            [
                'HTTP_Stripe-Signature' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        )->assertNoContent();

        $order->refresh();
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame(PaymentChargeResult::STATUS_SUCCEEDED, $order->payment_status);
        Mail::assertSent(OrderPaidMail::class, 1);

        $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            [
                'HTTP_Stripe-Signature' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        )->assertNoContent();

        $this->assertSame(1, PaymentWebhookEvent::query()->where('event_id', 'evt_success_1')->count());
        Mail::assertSent(OrderPaidMail::class, 1);
    }

    public function test_stripe_webhook_records_failed_payment(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $order = $this->createOrder($user, [
            'total' => 25.00,
            'payment_driver' => 'stripe',
            'external_payment_id' => 'pi_test_failed',
        ]);

        $payload = $this->stripeEventPayload([
            'id' => 'pi_test_failed',
            'amount' => 2500,
            'currency' => 'usd',
            'status' => 'requires_payment_method',
            'last_payment_error' => ['message' => 'Your card was declined.'],
            'metadata' => ['order_id' => (string) $order->id],
        ], 'payment_intent.payment_failed', 'evt_failed_1');

        $signature = $this->stripeSignature($payload, self::WEBHOOK_SECRET);

        $this->call(
            'POST',
            route('stripe.webhook'),
            [],
            [],
            [],
            [
                'HTTP_Stripe-Signature' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        )->assertNoContent();

        $order->refresh();
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame(PaymentChargeResult::STATUS_FAILED, $order->payment_status);
        $this->assertSame('Your card was declined.', $order->payment_failure_message);
    }

    /**
     * @param  array<string, mixed>  $intent
     */
    private function stripeEventPayload(array $intent, string $type, string $eventId): string
    {
        return json_encode([
            'id' => $eventId,
            'object' => 'event',
            'api_version' => '2024-11-20.acacia',
            'type' => $type,
            'data' => [
                'object' => array_merge(['object' => 'payment_intent'], $intent),
            ],
        ], JSON_THROW_ON_ERROR);
    }

    private function stripeSignature(string $payload, string $secret): string
    {
        $timestamp = time();
        $signedPayload = $timestamp.'.'.$payload;
        $signature = hash_hmac('sha256', $signedPayload, $secret);

        return 't='.$timestamp.',v1='.$signature;
    }
}
