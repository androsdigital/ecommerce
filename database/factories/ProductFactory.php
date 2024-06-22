<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $category = Category::inRandomOrder()->first() ?? Category::factory()->create();

        return [
            'category_id' => $category->id,
            'name'        => $this->faker->unique()->catchPhrase(),
            'slug'        => fn (array $attributes): string => Str::slug($attributes['name']),
            'description' => $this->faker->paragraph(),
            'features'    => [
                [
                    'name'  => 'Material',
                    'value' => $this->faker->word(),
                ],
                [
                    'name'  => 'Weight',
                    'value' => $this->faker->randomFloat(2, 0.1, 10),
                ],
            ],
            'comments' => [
                [
                    'comment' => $this->faker->paragraph(),
                ],
                [
                    'comment' => $this->faker->paragraph(),
                ],
            ],
        ];
    }
}
