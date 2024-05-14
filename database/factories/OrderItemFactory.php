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
            'stock_item_id' => $stockItems->random(),
            'quantity'      => $this->faker->numberBetween(1, 10),
            'unit_price'    => $this->faker->randomNumber(4),
        ];
    }
}
