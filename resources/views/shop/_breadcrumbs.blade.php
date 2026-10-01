<nav aria-label="Breadcrumb" class="store-breadcrumbs mb-8">
    <ol>
        <li><a href="{{ route('home') }}" class="store-footer-link">Home</a></li>
        <li aria-hidden="true">/</li>
        <li><a href="{{ route('shop.index') }}" class="store-footer-link">Shop</a></li>
        @if (! empty($categoryName))
            <li aria-hidden="true">/</li>
            <li>
                @if (! empty($productName))
                    <a href="{{ route('shop.index', ['category_id' => $categoryId ?? null]) }}" class="store-footer-link">{{ $categoryName }}</a>
                @else
                    <span class="font-medium text-ink" aria-current="page">{{ $categoryName }}</span>
                @endif
            </li>
        @endif
        @if (! empty($productName))
            <li aria-hidden="true">/</li>
            <li class="font-medium text-ink" aria-current="page">{{ $productName }}</li>
        @endif
    </ol>
</nav>
