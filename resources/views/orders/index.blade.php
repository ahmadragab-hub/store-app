@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    <section class="w-full">
        <div>
            <p class="text-sm font-medium text-accent">Account</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">Your orders</h1>
        </div>

        <div class="mt-8 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            @forelse ($orders as $order)
                @if ($loop->first)
                    <div class="hidden grid-cols-12 gap-4 border-b border-stone-200 bg-stone-50 px-6 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 sm:grid">
                        <span class="col-span-3">Order</span>
                        <span class="col-span-3">Date</span>
                        <span class="col-span-2">Status</span>
                        <span class="col-span-2">Total</span>
                        <span class="col-span-2 text-right"> </span>
                    </div>
                @endif
                <div class="grid grid-cols-1 gap-2 border-b border-stone-100 px-6 py-4 last:border-b-0 sm:grid-cols-12 sm:items-center">
                    <div class="font-medium text-stone-900 sm:col-span-3">#{{ $order->id }}</div>
                    <div class="text-sm text-stone-600 sm:col-span-3">{{ $order->created_at?->format('M j, Y') }}</div>
                    <div class="text-sm text-stone-600 sm:col-span-2">{{ $order->status }}</div>
                    <div class="text-sm text-stone-900 sm:col-span-2">${{ number_format((float) $order->total, 2) }}</div>
                    <div class="sm:col-span-2 sm:text-right">
                        <a href="{{ route('orders.show', $order) }}" class="text-sm font-medium text-stone-700 hover:text-stone-900">View</a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-sm text-stone-600">You have no orders yet.</div>
            @endforelse
        </div>

        @if ($orders->hasPages())
            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
