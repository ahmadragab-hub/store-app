@extends('layouts.app')

@section('title', 'Order #'.$order->id)

@section('content')
    <section class="w-full max-w-lg">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm font-medium text-accent">Order #{{ $order->id }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-stone-900">{{ ucfirst($order->status) }}</h1>
            <p class="mt-2 text-sm text-stone-600">{{ $order->created_at?->format('M j, Y g:i A') }}</p>

            <ul class="mt-8 divide-y divide-stone-100 border-y border-stone-100">
                @foreach ($order->items as $item)
                    <li class="flex items-center justify-between py-3 text-sm">
                        <span class="text-stone-700">{{ $item->product?->name ?? 'Product' }} × {{ $item->quantity }}</span>
                        <span class="font-medium text-stone-900">${{ number_format($item->quantity * (float) $item->price, 2) }}</span>
                    </li>
                @endforeach
            </ul>

            <p class="mt-4 text-lg font-semibold text-stone-900">Total ${{ number_format((float) $order->total, 2) }}</p>
            @if ($order->status === 'pending' && auth()->id() === $order->user_id)
                <a href="{{ route('checkout.pay', $order) }}" class="store-button mt-6">Pay this order</a>
            @endif
            <a href="{{ route('orders.index') }}" class="mt-6 inline-block text-sm font-medium text-stone-600 hover:text-stone-900">Back to orders</a>
        </div>
    </section>
@endsection
