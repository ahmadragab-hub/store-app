<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maximum quantity per cart line
    |--------------------------------------------------------------------------
    |
    | Stock checks still apply in CartService; this caps obviously abusive input.
    |
    */

    'max_line_quantity' => (int) env('CART_MAX_LINE_QUANTITY', 99),

];
