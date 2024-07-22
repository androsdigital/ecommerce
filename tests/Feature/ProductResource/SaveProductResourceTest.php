<?php

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Models\Product;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can render edit page', function () {
    $this->get(ProductResource::getUrl('edit', [
        'record' => Product::factory()->create(),
    ]))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can save a product', function () {
    $product = Product::factory()->create();
    $newData = Product::factory()->make();

    livewire(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('category_id')
        ->assertFormFieldExists('slug')
        ->assertFormFieldExists('description')
        ->fillForm([
            'category_id' => $newData->category_id,
            'name'        => $newData->name,
            'slug'        => $newData->slug,
            'description' => $newData->description,
            'features'    => $newData->features,
            'comments'    => $newData->comments,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'category_id' => $newData->category_id,
        'name'        => $newData->name,
        'slug'        => $newData->slug,
        'description' => $newData->description,
    ]);

    $product = Product::query()->where('slug', $newData->slug)->first();

    $this->assertEquals($newData->features[0], $product->features[2]);
    $this->assertEquals($newData->features[1], $product->features[3]);
    $this->assertEquals($newData->comments[0], $product->comments[2]);
    $this->assertEquals($newData->comments[1], $product->comments[3]);

    $this->assertAuthenticated();
});

it('can retrieve data', function () {
    $product = Product::factory()->create();

    livewire(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertFormSet([
            'name'                  => $product->name,
            'slug'                  => $product->slug,
            'category_id'           => $product->category_id,
            'description'           => $product->description,
            'price'                 => $product->price,
            'price_before_discount' => $product->price_before_discount,
        ])
        ->assertSeeHtml([ // This is because livewire render with UUID keys
            $product->features[0]['name'],
            $product->features[0]['value'],
            $product->comments[0]['comment'],
            $product->features[1]['name'],
            $product->features[1]['value'],
            $product->comments[1]['comment'],
        ]);

    $this->assertAuthenticated();
});

it('can validate edit input', function () {
    $product = Product::factory()->create();

    livewire(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
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
        ->call('save')
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
        ->call('save')
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
        ->call('save')
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
        ->call('save')
        ->assertHasFormErrors([
            'features.0.name' => 'alpha',
        ]);

    $this->assertAuthenticated();
});

it('can delete a product', function () {
    $address = Product::factory()->create();

    livewire(EditProduct::class, [
        'record' => $address->getRouteKey(), 0,
    ])
        ->callAction(DeleteAction::class)
        ->assertActionHalted(DeleteAction::class);

    $this->assertModelMissing($address);

    $this->assertAuthenticated();
});
