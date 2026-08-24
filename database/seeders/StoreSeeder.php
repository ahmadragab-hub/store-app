<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::create(['name' => 'Electronics']);
        $clothing = Category::create(['name' => 'Clothing']);

        Product::create([
            'category_id' => $electronics->id,
            'name' => 'Wireless Headphones',
            'description' => 'Over-ear Bluetooth headphones.',
            'price' => 59.99,
            'stock' => 20,
            'status' => 'active',
        ]);

        Product::create([
            'category_id' => $electronics->id,
            'name' => 'USB-C Charger',
            'description' => '30W fast charger.',
            'price' => 19.99,
            'stock' => 50,
            'status' => 'active',
        ]);

        Product::create([
            'category_id' => $clothing->id,
            'name' => 'Cotton T-Shirt',
            'description' => 'Unisex everyday tee.',
            'price' => 14.50,
            'stock' => 40,
            'status' => 'active',
        ]);
    }
}
