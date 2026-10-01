<?php

/**
 * Demo-only presentation hints (no effect on checkout, pricing, or APIs).
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Featured product names (must match seeded catalog names)
    |--------------------------------------------------------------------------
    */
    'featured_product_names' => [
        'NovaSound Wireless Earbuds',
        'PulseFit Smart Watch',
        'BrewCraft Pour-Over Kettle',
        'TrailFlex Hiking Boots',
        'FocusDesk Ergonomic Chair',
        'StarQuest Board Game',
        'Mountain Roast Coffee Beans',
    ],

    /*
    |--------------------------------------------------------------------------
    | Treat products created within this many days as "New"
    |--------------------------------------------------------------------------
    */
    'new_within_days' => 14,

    /*
    |--------------------------------------------------------------------------
    | Stock at or below this value shows "Low stock" (when stock > 0)
    |--------------------------------------------------------------------------
    */
    'low_stock_threshold' => 3,

];
