<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $users = collect(User::pluck('id'));

        return [
            'user_id'     => $users->random(),
            'number'      => 'OR' . $this->faker->unique()->randomNumber(6),
            'total_price' => $this->faker->randomNumber(5),
            'status'      => $this->faker->randomElement(['processing', 'shipped', 'delivered', 'cancelled']),
            'notes'       => $this->faker->realText(100),
            'created_at'  => $this->faker->dateTimeBetween('-1 year'),
            'updated_at'  => $this->faker->dateTimeBetween('-5 month'),
        ];
    }
}
