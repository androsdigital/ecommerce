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
        $city = City::inRandomOrder()->first() ?? City::factory()->create();
        $streetType = fake()->randomElement(StreetType::cases());
        $streetNumber = fake()->bothify('##?');
        $firstNumber = fake()->bothify('##?');
        $secondNumber = fake()->bothify('##?');
        $apartment = fake()->bothify('####');

        return [
            'city_id'       => $city->id,
            'customer_id'   => null,
            'street_type'   => $streetType->value,
            'street_number' => $streetNumber,
            'first_number'  => $firstNumber,
            'second_number' => $secondNumber,
            'apartment'     => $apartment,
            'building'      => fake()->sentence(3),
            'phone'         => fake()->unique()->phoneNumber(),
            'observation'   => fake()->paragraph(),
            'full_address'  => $city->name
                . ' - ' . $city->state->name
                . ', ' . $streetType->getLabel()
                . ' ' . $streetNumber
                . ' # ' . $firstNumber
                . ' - ' . $secondNumber
                . ' Apto ' . $apartment,
        ];
    }
}
