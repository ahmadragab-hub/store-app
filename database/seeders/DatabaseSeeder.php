<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $customer = User::query()->firstOrNew(['email' => 'customer@store.test']);
        $customer->forceFill([
            'name' => 'Demo Customer',
            'password' => 'password',
            'role' => 'customer',
            'email_verified_at' => now(),
        ])->save();

        $admin = User::query()->firstOrNew(['email' => 'admin@store.test']);
        $admin->forceFill([
            'name' => 'Demo Admin',
            'password' => 'password',
            'role' => 'admin',
            'email_verified_at' => now(),
        ])->save();

        $this->call([
            StoreSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
