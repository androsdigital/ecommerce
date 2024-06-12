<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $customer = Customer::inRandomOrder()->first();
        $address = $customer->addresses()->inRandomOrder()->first();

        return [
            'customer_id' => $customer->id,
            'address_id'  => $address->id,

            'number'                      => 'OR' . fake()->unique()->randomNumber(6),
            'total_price_before_discount' => 0,
            'total_items_discount'        => 0,
            'discount'                    => $this->faker->randomNumber(2),
            'total_shipping_price'        => 0,
            'total_quantity'              => 0,
            'status'                      => fake()->randomElement(OrderStatus::values()),
            'notes'                       => json_encode(fake()->realText(100)),
            'created_at'                  => fake()->dateTimeBetween('-2 month'),
            'updated_at'                  => fake()->dateTimeBetween('-1 month'),
        ];
    }
}
