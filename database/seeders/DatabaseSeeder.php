<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StateSeeder::class,
            CitySeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            SizeSeeder::class,
            ColorSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            StockItemSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
