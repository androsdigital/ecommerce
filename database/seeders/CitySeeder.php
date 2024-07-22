<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $stream = fopen(__DIR__ . '/cities_and_states.csv', 'r');

        while ($city = fgetcsv($stream)) {
            $state = State::firstOrCreate([
                'name' => $city[0],
            ]);

            City::create([
                'state_id' => $state->id,
                'name'     => $city[1],
            ]);
        }
    }
}
