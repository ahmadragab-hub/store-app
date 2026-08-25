@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <section class="w-full max-w-lg">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-accent">Checkout</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Confirm your order</h1>
            <p class="mt-2 text-sm text-stone-600">You will pay on the next screen with a mock test card. The order stays pending until payment succeeds.</p>

            <ul class="mt-8 divide-y divide-stone-100 border-y border-stone-100">
                @foreach ($cart->items as $item)
                    <li class="flex items-center justify-between py-3 text-sm">
                        <span class="text-stone-700">{{ $item->product?->name }} × {{ $item->quantity }}</span>
                        <span class="font-medium text-stone-900">${{ number_format($item->quantity * (float) ($item->product?->price ?? 0), 2) }}</span>
                    </li>
                @endforeach
            </ul>

            <p class="mt-4 text-lg font-semibold text-stone-900">Total ${{ number_format((float) $total, 2) }}</p>

            <form method="POST" action="{{ route('checkout.store') }}" class="mt-8">
                @csrf
                <button type="submit" class="store-button">Continue to payment</button>
            </form>
        </div>
    </section>
@endsection
