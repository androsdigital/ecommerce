<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $products = Product::all();

        return [
            'product_id' => $products->random()->id,
            'price'      => $products->random()->price,
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
        ];
    }
}
