<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $states = State::pluck('id');

        return [
            'state_id' => $states->random(),
            'name'     => fake()->word(),
            'code'     => fake()->numerify('CIU-####'),
        ];
    }
}
