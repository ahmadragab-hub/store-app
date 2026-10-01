@php
    $badges = [];

    if ($product->status === 'active' && (int) $product->stock === 0) {
        $badges[] = ['label' => 'Out of stock', 'class' => 'product-badge product-badge--out'];
    } elseif ($product->status === 'active' && (int) $product->stock > 0 && (int) $product->stock <= (int) config('demo.low_stock_threshold', 3)) {
        $badges[] = ['label' => 'Low stock', 'class' => 'product-badge product-badge--low'];
    }

    if ($product->created_at && $product->created_at->greaterThanOrEqualTo(now()->subDays((int) config('demo.new_within_days', 14)))) {
        $badges[] = ['label' => 'New', 'class' => 'product-badge product-badge--new'];
    }

    if (in_array($product->name, config('demo.featured_product_names', []), true)) {
        $badges[] = ['label' => 'Featured', 'class' => 'product-badge product-badge--featured'];
    }
@endphp

@if ($badges !== [])
    <div class="product-badges" aria-hidden="false">
        @foreach ($badges as $badge)
            <span class="{{ $badge['class'] }}">{{ $badge['label'] }}</span>
        @endforeach
    </div>
@endif
