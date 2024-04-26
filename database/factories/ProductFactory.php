<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $categories = collect(Category::pluck('id'));

        $initPrice = $this->faker->randomNumber(random_int(3, 4));

        if ($this->faker->boolean(35)) {
            $priceBeforeDiscount = $initPrice;
            $price = $this->faker->numberBetween($initPrice / 4, $initPrice / 2);
        } else {
            $price = $initPrice;
            $priceBeforeDiscount = null;
        }

        return [
            'category_id'           => $categories->random(),
            'name'                  => $this->faker->unique()->catchPhrase(),
            'slug'                  => fn (array $attributes): string => Str::slug($attributes['name']),
            'description'           => $this->faker->paragraph(),
            'price'                 => $price,
            'price_before_discount' => $priceBeforeDiscount,
            'features'              => [
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

    public function configure(): ProductFactory
    {
        return $this->afterCreating(function (Product $product) {
            $files = collect();

            foreach (glob(database_path('factories/products/*.jpg')) as $filename) {
                $files->add($filename);
            }

            try {
                $product
                    ->addMedia($files->random())
                    ->preservingOriginal()
                    ->toMediaCollection();
            } catch (Exception $e) {
                return;
            }
        });
    }
}
