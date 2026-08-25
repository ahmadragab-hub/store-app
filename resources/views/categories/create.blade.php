@extends('layouts.app')

@section('title', 'Add category')

@section('content')
    <section class="w-full max-w-md">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-accent">Catalog</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Add a category</h1>
            <p class="mt-2 text-sm text-stone-600">Give the category a name. Image is optional.</p>

            <form method="POST" action="{{ route('categories.store') }}" class="mt-8 space-y-5">
                @include('categories._form', ['category' => $category, 'button' => 'Create category'])
            </form>
        </div>
    </section>
@endsection
