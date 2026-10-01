@extends('layouts.app')

@section('title', 'Add product')

@section('content')
    <section class="w-full max-w-lg">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-accent">Catalog</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Add a product</h1>
            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="mt-8 space-y-5">
                @include('products._form', ['product' => $product, 'categories' => $categories, 'button' => 'Create product'])
            </form>
        </div>
    </section>
@endsection
