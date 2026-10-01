<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_name')->nullable()->after('status');
            $table->string('shipping_line1')->nullable()->after('shipping_name');
            $table->string('shipping_city')->nullable()->after('shipping_line1');
            $table->string('shipping_state')->nullable()->after('shipping_city');
            $table->string('shipping_postal')->nullable()->after('shipping_state');
            $table->string('shipping_country', 2)->nullable()->after('shipping_postal');
            $table->string('payment_driver')->nullable()->after('shipping_country');
            $table->timestamp('paid_at')->nullable()->after('payment_driver');
            $table->timestamp('shipped_at')->nullable()->after('paid_at');
            $table->timestamp('cancelled_at')->nullable()->after('shipped_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_name',
                'shipping_line1',
                'shipping_city',
                'shipping_state',
                'shipping_postal',
                'shipping_country',
                'payment_driver',
                'paid_at',
                'shipped_at',
                'cancelled_at',
            ]);
        });
    }
};
