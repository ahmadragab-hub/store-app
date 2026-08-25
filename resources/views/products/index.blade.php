@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <section class="w-full">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-accent">Catalog</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Products</h1>
                <p class="mt-2 text-sm text-stone-600">Manage what appears in the shop.</p>
            </div>
            <a href="{{ route('products.create') }}" class="store-button-inline">Add product</a>
        </div>

        <div class="mt-8 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            @forelse ($products as $product)
                @if ($loop->first)
                    <div class="hidden grid-cols-12 gap-4 border-b border-stone-200 bg-stone-50 px-6 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 sm:grid">
                        <span class="col-span-4">Name</span>
                        <span class="col-span-2">Category</span>
                        <span class="col-span-2">Price</span>
                        <span class="col-span-1">Stock</span>
                        <span class="col-span-1">Status</span>
                        <span class="col-span-2 text-right">Actions</span>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-3 border-b border-stone-100 px-6 py-4 last:border-b-0 sm:grid-cols-12 sm:items-center sm:gap-4">
                    <div class="sm:col-span-4">
                        <a href="{{ route('products.show', $product) }}" class="font-medium text-stone-900 hover:underline">{{ $product->name }}</a>
                    </div>
                    <div class="text-sm text-stone-600 sm:col-span-2">{{ $product->category?->name }}</div>
                    <div class="text-sm text-stone-600 sm:col-span-2">${{ number_format((float) $product->price, 2) }}</div>
                    <div class="text-sm text-stone-600 sm:col-span-1">{{ $product->stock }}</div>
                    <div class="text-sm text-stone-600 sm:col-span-1">{{ $product->status }}</div>
                    <div class="flex items-center gap-3 sm:col-span-2 sm:justify-end">
                        <a href="{{ route('products.edit', $product) }}" class="text-sm font-medium text-stone-700 hover:text-stone-900">Edit</a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-800">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-stone-600">No products yet.</p>
                    <a href="{{ route('products.create') }}" class="mt-4 inline-block text-sm font-semibold text-stone-900 underline">Add the first product</a>
                </div>
            @endforelse
        </div>

        @if ($products->hasPages())
            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </section>
@endsection
