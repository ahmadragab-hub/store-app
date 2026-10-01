@extends('layouts.app')

@section('title', $product->name)

@section('content')
    @include('shop._breadcrumbs', [
        'categoryName' => $product->category?->name,
        'categoryId' => $product->category_id,
        'productName' => $product->name,
    ])

    <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">
        <div class="store-product-card__media aspect-square max-h-[640px] lg:max-h-none">
            @include('shop._product-badges', ['product' => $product])
            @if ($product->image_src)
                <img src="{{ $product->image_src }}" alt="{{ $product->name }}" width="1200" height="1200">
            @else
                <div class="flex h-full items-center justify-center bg-zinc-200">
                    <span class="font-display text-6xl font-semibold text-ink/15">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                </div>
            @endif
        </div>

        <div class="flex flex-col lg:py-4">
            <p class="store-eyebrow">{{ $product->category?->name }}</p>
            <h1 class="store-title mt-3 text-3xl lg:text-[2.5rem]">{{ $product->name }}</h1>
            <p class="mt-5 font-display text-3xl font-semibold tracking-tight text-ink">${{ number_format((float) $product->price, 2) }}</p>

            @if ($product->stock > 0)
                <p class="mt-3 text-sm">
                    @if ((int) $product->stock <= (int) config('demo.low_stock_threshold', 3))
                        <span class="font-semibold text-amber-800">Only {{ (int) $product->stock }} remaining</span>
                    @else
                        <span class="text-muted">{{ (int) $product->stock }} in stock — ships after payment</span>
                    @endif
                </p>
            @else
                <p class="mt-3 text-sm font-semibold text-red-700">Currently unavailable</p>
            @endif

            @if ($product->description)
                <div class="mt-8 border-t border-line pt-8">
                    <h2 class="store-label !text-ink">Overview</h2>
                    <p class="mt-3 max-w-prose text-sm leading-7 text-muted">{{ $product->description }}</p>
                </div>
            @endif

            @if ($product->stock > 0)
                <form method="POST" action="{{ route('cart.items.store') }}" class="store-surface mt-8 space-y-4 p-5 sm:p-6">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="max-w-[8rem]">
                        <label for="quantity" class="store-label">Quantity</label>
                        <input id="quantity" name="quantity" type="number" min="1" max="{{ $product->stock }}" value="1" required class="store-input">
                    </div>
                    <button type="submit" class="store-button">Add to cart</button>
                    @guest
                        <p class="text-xs text-muted">Guest cart supported — sign in before checkout.</p>
                    @endguest
                </form>
            @else
                <p class="mt-8 text-sm text-muted">
                    <a href="{{ route('shop.index', ['category_id' => $product->category_id]) }}" class="store-link">Browse similar items</a>
                </p>
            @endif
        </div>
    </div>

    @if ($relatedProducts->isNotEmpty())
        <section class="mt-20 border-t border-line pt-14">
            <p class="store-eyebrow">Related</p>
            <h2 class="font-display mt-2 text-2xl font-semibold text-ink">You may also like</h2>
            <div class="store-product-grid mt-10">
                @foreach ($relatedProducts as $related)
                    @include('shop._product-card', ['product' => $related])
                @endforeach
            </div>
        </section>
    @endif
@endsection
