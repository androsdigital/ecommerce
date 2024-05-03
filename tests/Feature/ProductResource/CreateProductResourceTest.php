<?php

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;

use function Pest\Livewire\livewire;

it('can render create page', function () {
    $this->get(ProductResource::getUrl('create'))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can create a product', function () {
    $size = Size::factory()->create();
    $color = Color::factory()->create();
    $category = Category::factory()->create();
    $newData = Product::factory()->for($category)->make();

    livewire(CreateProduct::class)
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('category_id')
        ->assertFormFieldExists('inventoryItems')
        ->assertFormFieldExists('slug')
        ->assertFormFieldExists('description')
        ->assertFormFieldExists('photo')
        ->assertFormFieldExists('price')
        ->assertFormFieldExists('price_before_discount')
        ->assertFormFieldExists('features')
        ->assertFormFieldExists('comments')
        ->set('data.inventoryItems')
        ->fillForm([
            'category_id'    => $newData->category_id,
            'name'           => $newData->name,
            'inventoryItems' => [
                [
                    'color_id' => $size->id,
                    'size_id'  => $color->id,
                    'quantity' => 10,
                ],
            ],
            'slug'                  => $newData->slug,
            'description'           => $newData->description,
            'photo'                 => $newData->photo,
            'price'                 => $newData->price / 100,
            'price_before_discount' => $newData->price_before_discount / 100,
            'features'              => $newData->features,
            'comments'              => $newData->comments,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'category_id'           => $newData->category_id,
        'name'                  => $newData->name,
        'slug'                  => $newData->slug,
        'description'           => $newData->description,
        'price'                 => $newData->price,
        'price_before_discount' => $newData->price_before_discount,
    ]);

    $this->assertDatabaseHas('inventory_items', [
        'product_id' => Product::query()->where('name', $newData->name)->first()->id,
        'color_id'   => $color->id,
        'size_id'    => $size->id,
        'quantity'   => 10,
    ]);

    $product = Product::query()->where('slug', $newData->slug)->first();

    $this->assertEquals($newData->features, $product->features);
    $this->assertEquals($newData->comments, $product->comments);

    $this->assertAuthenticated();
});

it('can validate create input', function () {
    livewire(CreateProduct::class)
        ->fillForm([
            'name'           => null,
            'category_id'    => null,
            'description'    => null,
            'price'          => null,
            'inventoryItems' => [
                [
                    'color_id' => null,
                    'size_id'  => null,
                    'quantity' => null,
                ],
            ],
            'features' => [
                [
                    'name'  => null,
                    'value' => null,
                ],
            ],
            'comments' => [
                [
                    'comment' => null,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name'                      => 'required',
            'category_id'               => 'required',
            'description'               => 'required',
            'price'                     => 'required',
            'inventoryItems.0.color_id' => 'required',
            'inventoryItems.0.size_id'  => 'required',
            'inventoryItems.0.quantity' => 'required',
            'features.0.name'           => 'required',
            'features.0.value'          => 'required',
            'comments.0.comment'        => 'required',
        ])
        ->fillForm([
            'name'           => str_repeat('a', 256),
            'description'    => str_repeat('a', 1001),
            'inventoryItems' => [
                [
                    'quantity' => 10000,
                ],
            ],
            'features' => [
                [
                    'name'  => str_repeat('a', 51),
                    'value' => str_repeat('a', 501),
                ],
            ],
            'comments' => [
                [
                    'comment' => str_repeat('a', 501),
                ],
            ],
            'price'                 => 10000001,
            'price_before_discount' => 10000001,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name'                      => 'max',
            'description'               => 'max',
            'inventoryItems.0.quantity' => 'max',
            'features.0.name'           => 'max',
            'features.0.value'          => 'max',
            'comments.0.comment'        => 'max',
            'price'                     => 'max',
            'price_before_discount'     => 'max',
        ])
        ->fillForm([
            'description'    => 'a',
            'inventoryItems' => [
                [
                    'quantity' => -1,
                ],
            ],
            'features' => [
                [
                    'name' => 'a',
                ],
            ],
            'comments' => [
                [
                    'comment' => 'a',
                ],
            ],
            'price'                 => -1,
            'price_before_discount' => -1,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'description'               => 'min',
            'inventoryItems.0.quantity' => 'min',
            'comments.0.comment'        => 'min',
            'price'                     => 'min',
            'price_before_discount'     => 'min',
        ])
        ->fillForm([
            'inventoryItems' => [
                [
                    'quantity' => 100.4,
                ],
            ],
            'features' => [
                [
                    'name' => 100,
                ],
            ],
            'price'                 => 100.4,
            'price_before_discount' => 102.4,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'inventoryItems.0.quantity' => 'integer',
            'features.0.name'           => 'alpha',
            'price'                     => 'integer',
            'price_before_discount'     => 'integer',
        ])
        ->fillForm([
            'price'                 => 100,
            'price_before_discount' => 99,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'price_before_discount' => 'gte',
        ]);

    $this->assertAuthenticated();
});
