<x-mail::message>
# Payment received

Thanks {{ $order->shipping_name }}. Order **#{{ $order->id }}** is confirmed.

**Total:** ${{ number_format((float) $order->total, 2) }}

**Ship to**  
{{ $order->shipping_name }}  
{{ $order->shipping_line1 }}  
{{ $order->shipping_city }}@if($order->shipping_state), {{ $order->shipping_state }}@endif {{ $order->shipping_postal }}  
{{ $order->shipping_country }}

@foreach ($order->items as $item)
- {{ $item->product?->name ?? 'Product' }} × {{ $item->quantity }} (${{ number_format((float) $item->price, 2) }})
@endforeach

<x-mail::button :url="route('orders.show', $order)">
View order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
