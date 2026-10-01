@extends('layouts.app')

@section('title', 'Shop')

@section('content')
    @php
        $categoryId = request()->integer('category_id');
        $activeCategory = $categories->firstWhere('id', $categoryId);
        $hasFilters = request()->filled('q') || $categoryId || request('sort', 'latest') !== 'latest';
    @endphp

    @include('shop._breadcrumbs', [
        'categoryName' => $activeCategory?->name,
        'categoryId' => $categoryId,
    ])

    <header class="flex flex-wrap items-end justify-between gap-4 border-b border-line pb-8">
        <div>
            <p class="store-eyebrow">Catalog</p>
            <h1 class="store-title mt-2">
                {{ $activeCategory ? $activeCategory->name : 'All products' }}
            </h1>
            <p class="store-lede">Precision filters, clear sorting, and a catalog built for discovery.</p>
        </div>
        @if (! $products->isEmpty())
            <p class="text-sm font-medium text-muted">{{ $products->total() }} results</p>
        @endif
    </header>

    <div class="mt-6 flex flex-wrap items-center gap-3 lg:hidden">
        <button type="button" class="store-nav-filter-btn" data-shop-filters-open aria-expanded="false" aria-controls="shop-filters-drawer">
            Filters &amp; sort
        </button>
        @if ($categories->isNotEmpty())
            <div class="store-category-pills min-w-0 flex-1">
                <a href="{{ route('shop.index', request()->only(['q', 'sort'])) }}" class="store-filter {{ $categoryId ? 'store-filter-idle' : 'store-filter-active' }}">All</a>
                @foreach ($categories as $category)
                    <a href="{{ route('shop.index', array_merge(request()->only(['q', 'sort']), ['category_id' => $category->id])) }}" class="store-filter {{ $categoryId === $category->id ? 'store-filter-active' : 'store-filter-idle' }}">{{ $category->name }}</a>
                @endforeach
            </div>
        @endif
    </div>

    @if ($hasFilters)
        <div class="mt-6 flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wide text-muted">Active</span>
            @if (request()->filled('q'))
                <a href="{{ route('shop.index', array_merge(request()->except('q', 'page'), ['category_id' => $categoryId ?: null])) }}" class="store-filter-chip">“{{ request('q') }}” ×</a>
            @endif
            @if ($activeCategory)
                <a href="{{ route('shop.index', request()->only(['q', 'sort'])) }}" class="store-filter-chip">{{ $activeCategory->name }} ×</a>
            @endif
            @if (request('sort', 'latest') !== 'latest')
                <a href="{{ route('shop.index', array_merge(request()->except('sort', 'page'), ['category_id' => $categoryId ?: null])) }}" class="store-filter-chip">Sort reset ×</a>
            @endif
            <a href="{{ route('shop.index') }}" class="store-link text-xs">Clear all</a>
        </div>
    @endif

    <div id="shop-filters-drawer" class="store-filters-drawer" data-shop-filters-drawer aria-hidden="true">
        <button type="button" class="store-filters-drawer__backdrop" data-shop-filters-close aria-label="Close filters"></button>
        <div class="store-filters-drawer__panel" role="dialog" aria-modal="true" aria-label="Filters">
            <div class="mb-4 flex items-center justify-between">
                <p class="font-display text-lg font-semibold">Filters</p>
                <button type="button" class="store-nav-link" data-shop-filters-close>Close</button>
            </div>
            @include('shop._filters', ['suffix' => 'drawer'])
        </div>
    </div>

    <div class="store-shop-layout mt-8 lg:mt-10">
        <aside class="store-shop-sidebar" aria-label="Filters">
            @include('shop._filters', ['suffix' => 'sidebar'])
        </aside>

        <div class="min-w-0">
            @if ($products->isEmpty())
                @include('storefront._empty', [
                    'title' => 'No products found',
                    'message' => 'Adjust filters or explore the full catalog.',
                    'actionUrl' => route('shop.index'),
                    'actionLabel' => 'View all products',
                ])
            @else
                <div class="store-product-grid">
                    @foreach ($products as $product)
                        @include('shop._product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="store-pagination mt-12">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
