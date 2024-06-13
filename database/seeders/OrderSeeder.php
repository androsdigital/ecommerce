<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
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
                $order->OrderItems()->saveMany(OrderItem::factory(3)->make());

                $order->total_price_before_discount = $order->orderItems->sum(function (OrderItem $orderItem): int {
                    return $orderItem->stockItem->price_before_discount;
                });

                $order->total_items_discount = $order->orderItems->sum(function (OrderItem $orderItem): int {
                    return $orderItem->stockItem->discount;
                });

                $order->total_shipping_price = $order->orderItems->sum('shipping_price');
                $order->total_quantity = $order->orderItems()->sum('quantity');
                $order->total_discount = $order->total_items_discount + $order->discount;
                $order->total_price = $order->total_price_before_discount
                    - $order->total_discount
                    + $order->total_shipping_price;

                $order->save();
            });
    }
}
