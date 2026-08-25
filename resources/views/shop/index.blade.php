@extends('layouts.app')

@section('title', 'Shop')

@section('content')
    <section class="w-full">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-accent">Catalog</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Shop</h1>
                <p class="mt-2 text-sm text-stone-600">Active products available to buy.</p>
            </div>
        </div>

        @if ($categories->isNotEmpty())
            <div class="mt-6 flex flex-wrap gap-2">
                <a href="{{ route('shop.index') }}" class="rounded-full px-3 py-1.5 text-sm font-medium {{ request()->integer('category_id') ? 'bg-white text-stone-700 ring-1 ring-stone-200' : 'bg-stone-900 text-white' }}">
                    All
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('shop.index', ['category_id' => $category->id]) }}" class="rounded-full px-3 py-1.5 text-sm font-medium {{ request()->integer('category_id') === $category->id ? 'bg-stone-900 text-white' : 'bg-white text-stone-700 ring-1 ring-stone-200' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($products->isEmpty())
            <p class="mt-10 text-sm text-stone-600">No products in this category.</p>
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    @include('shop._product-card', ['product' => $product])
                @endforeach
            </div>
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </section>
@endsection
