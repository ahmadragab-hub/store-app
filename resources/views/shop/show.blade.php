@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <section class="grid w-full gap-8 lg:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
            @if ($product->image_src)
                <img src="{{ $product->image_src }}" alt="{{ $product->name }}" class="h-80 w-full object-cover">
            @else
                <div class="flex h-80 items-center justify-center bg-stone-100 text-sm text-stone-400">No image</div>
            @endif
        </div>

        <div>
            <p class="text-sm font-medium text-accent">{{ $product->category?->name }}</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-stone-900">{{ $product->name }}</h1>
            <p class="mt-3 text-xl font-semibold text-stone-900">${{ number_format((float) $product->price, 2) }}</p>
            <p class="mt-2 text-sm text-stone-600">{{ $product->stock }} in stock</p>
            @if ($product->description)
                <p class="mt-6 text-sm leading-6 text-stone-700">{{ $product->description }}</p>
            @endif

            @auth
                @if ($product->stock > 0)
                    <form method="POST" action="{{ route('cart.items.store') }}" class="mt-8 max-w-xs space-y-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div>
                            <label for="quantity" class="store-label">Quantity</label>
                            <input id="quantity" name="quantity" type="number" min="1" max="{{ $product->stock }}" value="1" required class="store-input">
                        </div>
                        <button type="submit" class="store-button">Add to cart</button>
                    </form>
                @else
                    <p class="mt-8 text-sm font-medium text-red-700">Out of stock.</p>
                @endif
            @else
                <p class="mt-8 text-sm text-stone-600">
                    <a href="{{ route('login') }}" class="font-semibold text-stone-900 underline">Log in</a>
                    to add this to your cart.
                </p>
            @endauth
        </div>
    </section>
@endsection
