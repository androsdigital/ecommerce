<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $colors = ['blue', 'red', 'green', 'yellow', 'black', 'white', 'purple', 'orange', 'pink', 'brown'];

        foreach ($colors as $color) {
            Color::create([
                'name' => $color,
            ]);
        }
    }
}
