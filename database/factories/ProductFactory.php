<?php

namespace Database\Factories;

use Exception;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'slug'                  => fn(array $attributes): string => Str::slug($attributes['name']),
            'description'           => $this->faker->paragraph(),
            'price'                 => $price,
            'price_before_discount' => $priceBeforeDiscount,
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
