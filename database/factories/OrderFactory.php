<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Address;
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
        $customer = Customer::inRandomOrder()->first() ?? Customer::factory()->create();
        $address = $customer->addresses()->inRandomOrder()->first() ?? Address::factory()->create();
        $discount = $this->faker->randomNumber(2);

        return [
            'customer_id' => $customer->id,
            'address_id'  => $address->id,

            'number'                      => $this->faker->unique()->numerify('OR-######'),
            'total_price'                 => 0,
            'total_price_before_discount' => 0,
            'total_items_discount'        => 0,
            'discount'                    => $discount,
            'total_discount'              => $discount,
            'total_shipping_price'        => 0,
            'total_quantity'              => 0,
            'status'                      => $this->faker->randomElement(OrderStatus::values()),
            'notes'                       => $this->faker->realText(100),
            'created_at'                  => $this->faker->dateTimeBetween('-2 month'),
            'updated_at'                  => $this->faker->dateTimeBetween('-1 month'),
        ];
    }
}
