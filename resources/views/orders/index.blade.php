@extends('layouts.app')

@section('title', $adminMode ? 'All orders' : 'Orders')

@section('content')
    <p class="store-eyebrow">{{ $adminMode ? 'Administration' : 'Account' }}</p>
    <h1 class="store-title mt-2">{{ $adminMode ? 'All orders' : 'Your orders' }}</h1>
    <p class="store-lede">{{ $adminMode ? 'Manage fulfillment across customers.' : 'Track status, totals, and shipment details.' }}</p>

    <div class="store-surface mt-10 overflow-hidden">
        @forelse ($orders as $order)
            <div class="store-order-row">
                <div class="font-display font-semibold text-ink sm:col-span-2">#{{ $order->id }}</div>
                @if ($adminMode)
                    <div class="truncate text-sm text-muted sm:col-span-3">{{ $order->user?->email }}</div>
                @endif
                <div class="text-sm text-muted {{ $adminMode ? 'sm:col-span-2' : 'sm:col-span-3' }}">{{ $order->created_at?->format('M j, Y') }}</div>
                <div class="sm:col-span-2">
                    <span class="store-status store-status-{{ $order->status }}">{{ $order->status }}</span>
                </div>
                <div class="font-display text-sm font-semibold sm:col-span-2">${{ number_format((float) $order->total, 2) }}</div>
                <div class="sm:col-span-1 sm:text-right">
                    <a href="{{ route('orders.show', $order) }}" class="store-link text-sm">Details</a>
                </div>
            </div>
        @empty
            <div class="p-10">
                @include('storefront._empty', [
                    'title' => 'No orders yet',
                    'message' => $adminMode ? 'Orders will appear here as customers checkout.' : 'When you purchase, orders show up here.',
                    'actionUrl' => $adminMode ? route('shop.index') : route('shop.index'),
                    'actionLabel' => 'Browse shop',
                ])
            </div>
        @endforelse
    </div>

    @if ($orders->hasPages())
        <div class="store-pagination mt-8">{{ $orders->links() }}</div>
    @endif
@endsection
