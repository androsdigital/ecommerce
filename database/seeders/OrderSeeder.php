<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::factory(100)
            ->create()
            ->each(function ($order) {
                $order->OrderItems()->saveMany(OrderItem::factory(3)->make());
            });
    }
}
