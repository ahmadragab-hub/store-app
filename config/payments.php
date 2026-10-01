<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default payment driver
    |--------------------------------------------------------------------------
    |
    | Supported: "mock", "stripe"
    | Use "mock" locally. For Stripe set PAYMENT_DRIVER=stripe, STRIPE_SECRET,
    | and STRIPE_WEBHOOK_SECRET. Never collect raw card numbers in production.
    |
    */

    'driver' => env('PAYMENT_DRIVER', 'mock'),

    'currency' => env('PAYMENT_CURRENCY', 'usd'),

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'mock' => [
        'test_card' => '4242424242424242',
    ],

];
