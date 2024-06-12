<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $stockItems = StockItem::pluck('id');
        $orders = Order::pluck('id');

        return [
            'order_id'       => $orders->random(),
            'stock_item_id'  => $stockItems->random(),
            'shipping_price' => $this->faker->randomNumber(2),
            'quantity'       => $this->faker->numberBetween(1, 10),
        ];
    }
}
