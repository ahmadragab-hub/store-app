@extends('layouts.app')

@section('title', 'Pay order #'.$order->id)

@section('content')
    <div class="mx-auto max-w-xl">
        <div class="mb-8 flex flex-wrap gap-2">
            <span class="store-checkout-step is-complete">1. Shipping</span>
            <span class="store-checkout-step is-active">2. Payment</span>
        </div>

        <div class="store-surface p-6 sm:p-8">
            <p class="store-eyebrow">Payment</p>
            <h1 class="font-display mt-2 text-2xl font-semibold text-ink">Complete order #{{ $order->id }}</h1>

            @if ($driver === 'mock')
                <div class="mt-4 rounded-sm border border-accent/20 bg-accent-soft px-4 py-3 text-sm text-ink">
                    <span class="font-semibold">Demo mode.</span> Use test card
                    <span class="font-mono font-medium">{{ $testCard }}</span> — no real charges.
                </div>
            @else
                <p class="mt-3 text-sm text-muted">Enter your Stripe payment method ID. Card numbers are not collected on this server.</p>
            @endif

            <p class="mt-6 font-display text-3xl font-semibold text-ink">${{ number_format((float) $order->total, 2) }}</p>

            <form method="POST" action="{{ route('checkout.pay.store', $order) }}" class="mt-8 space-y-5">
                @csrf
                @if ($driver === 'stripe')
                    <div>
                        <label for="payment_token" class="store-label">Payment method ID</label>
                        <input id="payment_token" name="payment_token" type="text" value="{{ old('payment_token') }}" required class="store-input" placeholder="pm_...">
                        @error('payment_token')<p class="store-error">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="store-button">Pay with Stripe</button>
                @else
                    <div>
                        <label for="card_number" class="store-label">Card number (test)</label>
                        <input id="card_number" name="card_number" type="text" inputmode="numeric" autocomplete="cc-number" value="{{ old('card_number', $testCard) }}" required class="store-input">
                        @error('card_number')<p class="store-error">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="store-button">Pay now</button>
                @endif
            </form>
        </div>
    </div>
@endsection
