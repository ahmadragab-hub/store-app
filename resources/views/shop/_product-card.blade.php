<article class="store-product-card">
    <a href="{{ route('shop.show', $product) }}" class="store-product-card__link rounded-sm">
        <div class="store-product-card__media">
            @include('shop._product-badges', ['product' => $product])
            @if ($product->image_src)
                <img src="{{ $product->image_src }}" alt="{{ $product->name }}" loading="lazy" width="800" height="1000">
            @else
                <div class="flex h-full min-h-44 items-center justify-center bg-zinc-200">
                    <span class="font-display text-3xl font-semibold text-ink/20">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                </div>
            @endif
        </div>
        <div class="store-product-card__body">
            <p class="store-eyebrow !text-[10px]">{{ $product->category?->name }}</p>
            <h3 class="store-product-card__name">{{ $product->name }}</h3>
            <div class="store-product-card__meta">
                <p class="store-product-card__price">${{ number_format((float) $product->price, 2) }}</p>
                @if ($product->status === 'active' && (int) $product->stock === 0)
                    <span class="text-xs font-medium text-red-700">Sold out</span>
                @elseif ($product->status === 'active' && (int) $product->stock <= (int) config('demo.low_stock_threshold', 3))
                    <span class="text-xs font-medium text-amber-800">{{ (int) $product->stock }} left</span>
                @endif
            </div>
        </div>
    </a>
</article>
