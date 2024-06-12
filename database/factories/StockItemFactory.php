<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\StockItem;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockItem>
 */
class StockItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sizes = Size::pluck('id');
        $colors = Color::pluck('id');
        $products = Product::pluck('id');
        $addresses = Address::pluck('id');

        $priceBeforeDiscount = $this->faker->randomNumber(random_int(4, 5));

        if ($this->faker->boolean(35)) {
            $discount = $this->faker->numberBetween($priceBeforeDiscount / 10, $priceBeforeDiscount / 6);
        } else {
            $discount = 0;
        }

        return [
            'address_id'            => $addresses->random(),
            'size_id'               => $sizes->random(),
            'color_id'              => $colors->random(),
            'product_id'            => $products->random(),
            'sku'                   => $this->faker->unique()->numerify('SKU-######'),
            'quantity'              => $this->faker->numberBetween(100000, 1000000),
            'discount'              => $discount,
            'price_before_discount' => $priceBeforeDiscount,
        ];
    }

    public function configure(): Factory
    {
        return $this->afterCreating(function (StockItem $stockItem) {
            $files = collect();

            foreach (glob(database_path('factories/products/*.jpg')) as $filename) {
                $files->add($filename);
            }

            try {
                $stockItem
                    ->addMedia($files->random())
                    ->preservingOriginal()
                    ->toMediaCollection();
            } catch (Exception $e) {
                return;
            }
        });
    }
}
