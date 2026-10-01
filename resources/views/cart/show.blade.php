@extends('layouts.app')

@section('title', 'Cart')

@section('content')
    <div class="mx-auto max-w-4xl">
        <p class="store-eyebrow">Checkout</p>
        <h1 class="store-title mt-2">Shopping cart</h1>
        @if ($guest)
            <p class="store-lede">Guest session — <a href="{{ route('login') }}" class="store-link">log in</a> to checkout and save your bag.</p>
        @else
            <p class="store-lede">Review items before shipping and payment.</p>
        @endif

        @if ($items->isEmpty())
            <div class="mt-10">
                @include('storefront._empty', [
                    'title' => 'Your cart is empty',
                    'message' => 'Discover products tailored to your routine.',
                    'actionUrl' => route('shop.index'),
                    'actionLabel' => 'Continue shopping',
                ])
            </div>
        @else
            <div class="mt-10 grid gap-8 lg:grid-cols-3">
                <ul class="store-surface divide-y divide-line lg:col-span-2">
                    @foreach ($items as $item)
                        <li class="store-cart-line flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:p-6">
                            <div class="flex min-w-0 flex-1 gap-4">
                                @if ($item->product?->image_src)
                                    <a href="{{ $item->product ? route('shop.show', $item->product) : route('shop.index') }}" class="store-cart-thumb">
                                        <img src="{{ $item->product->image_src }}" alt="">
                                    </a>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ $item->product ? route('shop.show', $item->product) : '#' }}" class="font-semibold text-ink hover:text-accent">{{ $item->product?->name ?? 'Unavailable' }}</a>
                                    <p class="mt-1 text-sm text-muted">${{ number_format((float) ($item->product?->price ?? 0), 2) }} each</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                                @if ($guest)
                                    <form method="POST" action="{{ route('cart.guest.update', $item->product_id) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label class="sr-only" for="qty-g-{{ $item->product_id }}">Quantity</label>
                                        <input id="qty-g-{{ $item->product_id }}" name="quantity" type="number" min="1" max="{{ $item->product?->stock ?? $item->quantity }}" value="{{ $item->quantity }}" class="store-input !mt-0 w-20 !py-2">
                                        <button type="submit" class="store-link text-xs">Update</button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.guest.destroy', $item->product_id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-700">Remove</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('cart.items.update', $item) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label class="sr-only" for="qty-{{ $item->id }}">Quantity</label>
                                        <input id="qty-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ $item->product?->stock ?? $item->quantity }}" value="{{ $item->quantity }}" class="store-input !mt-0 w-20 !py-2">
                                        <button type="submit" class="store-link text-xs">Update</button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-700">Remove</button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                <aside class="store-surface h-fit p-6 lg:sticky lg:top-24">
                    <h2 class="store-label !text-ink">Summary</h2>
                    <p class="mt-4 font-display text-3xl font-semibold text-ink">${{ number_format((float) $total, 2) }}</p>
                    <p class="mt-2 text-xs text-muted">Taxes and shipping finalized at checkout.</p>
                    @auth
                        <a href="{{ route('checkout.create') }}" class="store-button mt-6">Proceed to checkout</a>
                    @else
                        <a href="{{ route('login') }}" class="store-button mt-6">Log in to checkout</a>
                    @endauth
                </aside>
            </div>
        @endif
    </div>
@endsection
