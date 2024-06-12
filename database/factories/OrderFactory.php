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

        $total_price_before_discount = fake()->randomNumber(5);

        return [
            'customer_id' => $customer->id,
            'address_id'  => $address->id,

            'number'                      => 'OR' . fake()->unique()->randomNumber(6),
            'total_price_before_discount' => $total_price_before_discount,
            'total_discount'              => intdiv($total_price_before_discount, 2),
            'total_shipping_price'        => fake()->randomNumber(3),
            'total_quantity'              => fake()->randomNumber(1),
            'status'                      => fake()->randomElement(OrderStatus::values()),
            'notes'                       => json_encode(fake()->realText(100)),
            'created_at'                  => fake()->dateTimeBetween('-2 month'),
            'updated_at'                  => fake()->dateTimeBetween('-1 month'),
        ];
    }
}
