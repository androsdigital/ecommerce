<?php

namespace Database\Factories;

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

        return [
            'stock_item_id'         => $stockItems->random(),
            'price_before_discount' => $this->faker->randomNumber(4),
            'discount'              => $this->faker->randomNumber(3),
            'shipping_price'        => $this->faker->randomNumber(3),
            'quantity'              => $this->faker->numberBetween(1, 10),
        ];
    }
}
