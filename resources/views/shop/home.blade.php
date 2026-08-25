@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
    <section class="w-full">
        <div>
            <p class="text-sm font-medium text-accent">Store</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-stone-900">Find something you like</h1>
            <p class="mt-2 max-w-xl text-sm text-stone-600">Browse categories and products. Log in to add items to your cart and checkout.</p>
            <a href="{{ route('shop.index') }}" class="store-button-inline mt-6">Browse all products</a>
        </div>

        @if ($categories->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-lg font-semibold text-stone-900">Categories</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $category)
                        <a href="{{ route('shop.index', ['category_id' => $category->id]) }}" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition hover:border-stone-400">
                            <p class="font-semibold text-stone-900">{{ $category->name }}</p>
                            <p class="mt-1 text-sm text-stone-600">{{ $category->products_count }} {{ $category->products_count === 1 ? 'product' : 'products' }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-12">
            <h2 class="text-lg font-semibold text-stone-900">Latest products</h2>
            @if ($products->isEmpty())
                <p class="mt-4 text-sm text-stone-600">No products yet.</p>
            @else
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($products as $product)
                        @include('shop._product-card', ['product' => $product])
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
