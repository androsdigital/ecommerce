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
        $stockItem = StockItem::inRandomOrder()->first() ?? StockItem::factory()->create();
        $order = Order::inRandomOrder()->first() ?? Order::factory()->create();
        $shippingPrice = $this->faker->randomNumber(3);
        $quantity = $this->faker->numberBetween(1, 10);

        return [
            'stock_item_id'  => $stockItem->id,
            'order_id'       => $order->id,
            'shipping_price' => $shippingPrice,
            'price'          => ($stockItem->price * $quantity) + $shippingPrice,
            'unit_price'     => $stockItem->price,
            'quantity'       => $quantity,
        ];
    }
}
