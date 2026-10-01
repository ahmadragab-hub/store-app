@extends('layouts.app')

@section('title', 'Order #'.$order->id)

@section('content')
    <div class="mx-auto max-w-2xl">
        <a href="{{ auth()->user()->isAdmin() ? route('admin.orders.index') : route('orders.index') }}" class="store-link text-sm">← Back to orders</a>

        <div class="store-surface mt-6 p-6 sm:p-8">
            <p class="store-eyebrow">Order #{{ $order->id }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <h1 class="font-display text-2xl font-semibold capitalize text-ink">{{ $order->status }}</h1>
                <span class="store-status store-status-{{ $order->status }}">{{ $order->status }}</span>
            </div>
            <p class="mt-2 text-sm text-muted">Placed {{ $order->created_at?->format('M j, Y g:i A') }}</p>

            @if ($order->shipping_line1)
                <div class="mt-8 rounded-sm border border-line bg-canvas/60 p-4 text-sm">
                    <p class="store-label !text-ink">Ship to</p>
                    <p class="mt-2 font-medium text-ink">{{ $order->shipping_name }}</p>
                    <p class="text-muted">{{ $order->shipping_line1 }}</p>
                    <p class="text-muted">{{ $order->shipping_city }}@if($order->shipping_state), {{ $order->shipping_state }}@endif {{ $order->shipping_postal }}</p>
                    <p class="text-muted">{{ $order->shipping_country }}</p>
                </div>
            @endif

            <h2 class="store-label mt-8 !text-ink">Items</h2>
            <ul class="mt-3 divide-y divide-line border-y border-line text-sm">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-4 py-3">
                        <span class="text-ink">{{ $item->product?->name ?? 'Product' }} × {{ $item->quantity }}</span>
                        <span class="font-medium">${{ number_format($item->quantity * (float) $item->price, 2) }}</span>
                    </li>
                @endforeach
            </ul>

            <p class="mt-6 flex justify-between font-display text-xl font-semibold">
                <span>Total</span>
                <span>${{ number_format((float) $order->total, 2) }}</span>
            </p>

            @if ($order->status === 'pending' && auth()->id() === $order->user_id)
                <a href="{{ route('checkout.pay', $order) }}" class="store-button mt-8">Complete payment</a>
            @endif

            @if (auth()->user()->isAdmin())
                <div class="mt-6 flex flex-wrap gap-3 border-t border-line pt-6">
                    @can('ship', $order)
                        <form method="POST" action="{{ route('admin.orders.ship', $order) }}">
                            @csrf
                            <button type="submit" class="store-button-inline">Mark shipped</button>
                        </form>
                    @endcan
                    @can('cancel', $order)
                        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}">
                            @csrf
                            <button type="submit" class="store-button-ghost !text-red-700">Cancel order</button>
                        </form>
                    @endcan
                </div>
            @endif
        </div>
    </div>
@endsection
