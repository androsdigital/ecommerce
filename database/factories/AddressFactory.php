<?php

namespace Database\Factories;

use App\Enums\StreetType;
use App\Models\Address;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        $city_id = collect(City::pluck('id'))->random();

        return [
            'city_id'       => $city_id,
            'customer_id'   => null,
            'street_type'   => fake()->randomElement(StreetType::values()),
            'street_number' => fake()->bothify('##?'),
            'first_number'  => fake()->bothify('##?'),
            'second_number' => fake()->bothify('##?'),
            'apartment'     => fake()->bothify('####'),
            'phone'         => fake()->unique()->phoneNumber(),
            'observation'   => fake()->paragraph(),
        ];
    }
}
