@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <section class="w-full">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-accent">Catalog</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Categories</h1>
                <p class="mt-2 text-sm text-stone-600">Create and manage the groups products belong to.</p>
            </div>
            <a href="{{ route('categories.create') }}" class="store-button-inline">Add category</a>
        </div>

        <div class="mt-8 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            @forelse ($categories as $category)
                @if ($loop->first)
                    <div class="hidden grid-cols-12 gap-4 border-b border-stone-200 bg-stone-50 px-6 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 sm:grid">
                        <span class="col-span-5">Name</span>
                        <span class="col-span-3">Image</span>
                        <span class="col-span-2">Products</span>
                        <span class="col-span-2 text-right">Actions</span>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-3 border-b border-stone-100 px-6 py-4 last:border-b-0 sm:grid-cols-12 sm:items-center sm:gap-4">
                    <div class="sm:col-span-5">
                        <a href="{{ route('categories.show', $category) }}" class="font-medium text-stone-900 hover:underline">
                            {{ $category->name }}
                        </a>
                    </div>
                    <div class="text-sm text-stone-600 sm:col-span-3">
                        @if ($category->image)
                            <span class="truncate">{{ $category->image }}</span>
                        @else
                            <span class="text-stone-400">No image</span>
                        @endif
                    </div>
                    <div class="text-sm text-stone-600 sm:col-span-2">{{ $category->products_count }}</div>
                    <div class="flex items-center gap-3 sm:col-span-2 sm:justify-end">
                        <a href="{{ route('categories.edit', $category) }}" class="text-sm font-medium text-stone-700 hover:text-stone-900">Edit</a>
                        <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-800">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-stone-600">No categories yet.</p>
                    <a href="{{ route('categories.create') }}" class="mt-4 inline-block text-sm font-semibold text-stone-900 underline decoration-stone-300 underline-offset-4 hover:decoration-stone-900">
                        Add the first category
                    </a>
                </div>
            @endforelse
        </div>
    </section>
@endsection
