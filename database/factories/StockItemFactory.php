<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\StockItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockItem>
 */
class StockItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sizes = Size::pluck('id');
        $colors = Color::pluck('id');
        $products = Product::pluck('id');
        $addresses = Address::pluck('id');

        return [
            'address_id' => $addresses->random(),
            'size_id'    => $sizes->random(),
            'color_id'   => $colors->random(),
            'product_id' => $products->random(),
            'sku'        => 'SKU-' . $this->faker->unique()->randomNumber(6),
            'quantity'   => $this->faker->numberBetween(1, 100),
        ];
    }
}
