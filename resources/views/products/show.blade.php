@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <section class="w-full max-w-lg">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-accent">{{ $product->category?->name }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">{{ $product->name }}</h1>
            <p class="mt-2 text-sm text-stone-600">${{ number_format((float) $product->price, 2) }} · {{ $product->stock }} in stock · {{ $product->status }}</p>
            @if ($product->description)
                <p class="mt-4 text-sm text-stone-700">{{ $product->description }}</p>
            @endif
            <div class="mt-8 flex items-center gap-3">
                <a href="{{ route('products.edit', $product) }}" class="store-button-inline">Edit</a>
                <a href="{{ route('products.index') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900">Back to list</a>
            </div>
        </div>
    </section>
@endsection
