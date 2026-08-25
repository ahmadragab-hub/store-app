<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayOrderRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\MockPaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $carts,
        private CheckoutService $checkout,
    ) {
    }

    public function create(): View|RedirectResponse
    {
        $cart = $this->carts->contents(auth()->user());

        if ($cart->items->isEmpty()) {
            return redirect()
                ->route('cart.show')
                ->with('error', 'Your cart is empty.');
        }

        $total = $cart->items->sum(function ($item) {
            return $item->quantity * (float) ($item->product?->price ?? 0);
        });

        return view('checkout.create', compact('cart', 'total'));
    }

    public function store(): RedirectResponse
    {
        $order = $this->checkout->checkout(auth()->user());

        return redirect()
            ->route('checkout.pay', $order)
            ->with('success', 'Order created. Pay with the test card to complete it.');
    }

    public function payForm(Order $order): View|RedirectResponse
    {
        abort_unless(auth()->user()->can('pay', $order), 404);

        if ($order->status === 'paid') {
            return redirect()
                ->route('orders.show', $order)
                ->with('success', 'This order is already paid.');
        }

        $order->load('items.product');

        return view('checkout.pay', [
            'order' => $order,
            'testCard' => MockPaymentGateway::TEST_CARD,
        ]);
    }

    public function pay(PayOrderRequest $request, Order $order): RedirectResponse
    {
        abort_unless($request->user()->can('pay', $order), 404);

        $this->checkout->pay($request->user(), $order, $request->string('card_number')->toString());

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Payment successful.');
    }
}
