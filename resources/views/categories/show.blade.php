@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <section class="w-full max-w-md">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-accent">Catalog</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">{{ $category->name }}</h1>
            <p class="mt-2 text-sm text-stone-600">{{ $category->products_count }} {{ $category->products_count === 1 ? 'product' : 'products' }} in this category.</p>

            @if ($category->image_src)
                <img src="{{ $category->image_src }}" alt="{{ $category->name }}" class="mt-6 h-40 w-full rounded-lg object-cover">
            @endif

            <div class="mt-8 flex items-center gap-3">
                <a href="{{ route('categories.edit', $category) }}" class="store-button-inline">Edit</a>
                <a href="{{ route('categories.index') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900">Back to list</a>
            </div>
        </div>
    </section>
@endsection
