<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Order::factory(100)
            ->create()
            ->each(function (Order $order) {
                $order->saveOrderItem(3);
            });
    }
}
