<?php

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Models\Product;

use function Pest\Livewire\livewire;

it('can render create page', function () {
    $this->get(ProductResource::getUrl('create'))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can create a product', function () {
    $newData = Product::factory()->create();

    livewire(CreateProduct::class)
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('category_id')
        ->assertFormFieldExists('slug')
        ->assertFormFieldExists('description')
        ->assertFormFieldExists('features')
        ->assertFormFieldExists('comments')
        ->set('data.stockItems')
        ->fillForm([
            'category_id' => $newData->category_id,
            'name'        => $newData->name,
            'slug'        => $newData->slug . '-new',
            'description' => $newData->description,
            'features'    => $newData->features,
            'comments'    => $newData->comments,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'category_id' => $newData->category_id,
        'name'        => $newData->name,
        'slug'        => $newData->slug . '-new',
        'description' => $newData->description,
    ]);

    $product = Product::query()->where('slug', $newData->slug . '-new')->first();

    $this->assertEquals($newData->features, $product->features);
    $this->assertEquals($newData->comments, $product->comments);

    $this->assertAuthenticated();
});

it('can validate create input', function () {
    Product::factory()->create();

    livewire(CreateProduct::class)
        ->fillForm([
            'name'        => null,
            'category_id' => null,
            'description' => null,
            'features'    => [
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
            'name'               => 'required',
            'category_id'        => 'required',
            'description'        => 'required',
            'features.0.name'    => 'required',
            'features.0.value'   => 'required',
            'comments.0.comment' => 'required',
        ])
        ->fillForm([
            'name'        => str_repeat('a', 256),
            'description' => str_repeat('a', 1001),
            'features'    => [
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
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name'               => 'max',
            'description'        => 'max',
            'features.0.name'    => 'max',
            'features.0.value'   => 'max',
            'comments.0.comment' => 'max',
        ])
        ->fillForm([
            'description' => 'a',
            'features'    => [
                [
                    'name' => 'a',
                ],
            ],
            'comments' => [
                [
                    'comment' => 'a',
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'description'        => 'min',
            'comments.0.comment' => 'min',
        ])
        ->fillForm([
            'features' => [
                [
                    'name' => 100,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'features.0.name' => 'alpha',
        ]);

    $this->assertAuthenticated();
});
