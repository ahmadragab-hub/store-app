<?php

namespace App\Console\Commands;

use App\Services\CheckoutService;
use Illuminate\Console\Command;

class ExpirePendingOrders extends Command
{
    protected $signature = 'orders:expire-pending';

    protected $description = 'Cancel expired pending orders and release their reserved stock';

    public function handle(CheckoutService $checkout): int
    {
        $expired = $checkout->expireStalePendingOrders();

        $this->info("Expired {$expired} pending order(s).");

        return self::SUCCESS;
    }
}
