@extends('layouts.app')

@section('title', 'Pay order #'.$order->id)

@section('content')
    <section class="w-full max-w-lg">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-accent">Mock payment</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Pay order #{{ $order->id }}</h1>
            <p class="mt-2 text-sm text-stone-600">No real card is charged. Use test card <span class="font-mono font-medium text-stone-900">{{ $testCard }}</span>.</p>

            <p class="mt-6 text-lg font-semibold text-stone-900">Total ${{ number_format((float) $order->total, 2) }}</p>

            <form method="POST" action="{{ route('checkout.pay.store', $order) }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="card_number" class="store-label">Card number</label>
                    <input id="card_number" name="card_number" type="text" inputmode="numeric" autocomplete="cc-number" value="{{ old('card_number', $testCard) }}" required class="store-input">
                    @error('card_number')
                        <p class="store-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="store-button">Pay (test)</button>
            </form>
        </div>
    </section>
@endsection
