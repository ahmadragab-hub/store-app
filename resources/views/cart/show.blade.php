@extends('layouts.app')

@section('title', 'Cart')

@section('content')
    <section class="w-full">
        <div>
            <p class="text-sm font-medium text-accent">Checkout</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Your cart</h1>
        </div>

        @if ($cart->items->isEmpty())
            <p class="mt-8 text-sm text-stone-600">Your cart is empty. <a href="{{ route('shop.index') }}" class="font-semibold text-stone-900 underline">Continue shopping</a></p>
        @else
            <div class="mt-8 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                @foreach ($cart->items as $item)
                    <div class="flex flex-col gap-4 border-b border-stone-100 px-6 py-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-medium text-stone-900">{{ $item->product?->name ?? 'Unavailable product' }}</p>
                            <p class="text-sm text-stone-600">${{ number_format((float) ($item->product?->price ?? 0), 2) }} each</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <form method="POST" action="{{ route('cart.items.update', $item) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input name="quantity" type="number" min="1" max="{{ $item->product?->stock ?? $item->quantity }}" value="{{ $item->quantity }}" class="store-input mt-0 w-20 py-1.5">
                                <button type="submit" class="text-sm font-medium text-stone-700 hover:text-stone-900">Update</button>
                            </form>
                            <form method="POST" action="{{ route('cart.items.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-800">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-lg font-semibold text-stone-900">Total ${{ number_format((float) $total, 2) }}</p>
                <a href="{{ route('checkout.create') }}" class="store-button-inline">Checkout</a>
            </div>
        @endif
    </section>
@endsection
