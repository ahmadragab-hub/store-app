@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-8 flex flex-wrap gap-2">
            <span class="store-checkout-step is-active">1. Shipping</span>
            <span class="store-checkout-step">2. Payment</span>
        </div>

        <div class="grid gap-8 lg:grid-cols-5">
            <div class="store-surface p-6 sm:p-8 lg:col-span-3">
                <p class="store-eyebrow">Delivery</p>
                <h1 class="font-display mt-2 text-2xl font-semibold text-ink">Shipping information</h1>
                <p class="mt-2 text-sm text-muted">Stock is reserved when you submit this step.</p>

                <form method="POST" action="{{ route('checkout.store') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="shipping_name" class="store-label">Full name</label>
                        <input id="shipping_name" name="shipping_name" type="text" value="{{ old('shipping_name', auth()->user()->name) }}" required class="store-input">
                        @error('shipping_name')<p class="store-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="shipping_line1" class="store-label">Address</label>
                        <input id="shipping_line1" name="shipping_line1" type="text" value="{{ old('shipping_line1') }}" required class="store-input">
                        @error('shipping_line1')<p class="store-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="shipping_city" class="store-label">City</label>
                            <input id="shipping_city" name="shipping_city" type="text" value="{{ old('shipping_city') }}" required class="store-input">
                            @error('shipping_city')<p class="store-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="shipping_state" class="store-label">State / region</label>
                            <input id="shipping_state" name="shipping_state" type="text" value="{{ old('shipping_state') }}" class="store-input">
                            @error('shipping_state')<p class="store-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="shipping_postal" class="store-label">Postal code</label>
                            <input id="shipping_postal" name="shipping_postal" type="text" value="{{ old('shipping_postal') }}" required class="store-input">
                            @error('shipping_postal')<p class="store-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="shipping_country" class="store-label">Country (ISO)</label>
                            <input id="shipping_country" name="shipping_country" type="text" maxlength="2" value="{{ old('shipping_country', 'US') }}" required class="store-input">
                            @error('shipping_country')<p class="store-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button type="submit" class="store-button">Continue to payment</button>
                </form>
            </div>

            <aside class="store-surface h-fit p-6 lg:col-span-2 lg:sticky lg:top-24">
                <h2 class="store-label !text-ink">Order summary</h2>
                <ul class="mt-4 divide-y divide-line text-sm">
                    @foreach ($cart->items as $item)
                        <li class="flex justify-between gap-3 py-3">
                            <span class="text-muted">{{ $item->product?->name }} × {{ $item->quantity }}</span>
                            <span class="font-medium text-ink">${{ number_format($item->quantity * (float) ($item->product?->price ?? 0), 2) }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 flex justify-between border-t border-line pt-4 font-display text-lg font-semibold">
                    <span>Total</span>
                    <span>${{ number_format((float) $total, 2) }}</span>
                </p>
            </aside>
        </div>
    </div>
@endsection
