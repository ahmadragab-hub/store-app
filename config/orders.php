<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pending order reservation TTL
    |--------------------------------------------------------------------------
    |
    | Unpaid pending orders expire after this many minutes. Expiration releases
    | reserved stock back to inventory (same as cancelling a pending order).
    |
    */

    'pending_expires_after_minutes' => (int) env('ORDER_PENDING_EXPIRES_MINUTES', 60),

];
