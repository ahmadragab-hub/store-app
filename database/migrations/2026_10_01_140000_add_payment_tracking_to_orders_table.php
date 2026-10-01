<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status')->nullable()->after('payment_driver');
            $table->string('external_payment_id')->nullable()->after('payment_status');
            $table->string('payment_idempotency_key', 80)->nullable()->unique()->after('external_payment_id');
            $table->string('payment_failure_message')->nullable()->after('payment_idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'external_payment_id',
                'payment_idempotency_key',
                'payment_failure_message',
            ]);
        });
    }
};
