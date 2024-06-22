<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $sizes = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', 'unisize', 'no-size'];

        foreach ($sizes as $size) {
            Size::create([
                'name' => $size,
            ]);
        }
    }
}
