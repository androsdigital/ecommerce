<?php

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\UploadedFile;

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

    $photos = [];

    for ($i = 0; $i < 3; $i++) {
        $photos[] = UploadedFile::fake()->image('photo-' . $i . '.jpg');
    }

    livewire(CreateProduct::class)
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('category_id')
        ->assertFormFieldExists('stockItems')
        ->assertFormFieldExists('slug')
        ->assertFormFieldExists('description')
        ->assertFormFieldExists('photos')
        ->assertFormFieldExists('price')
        ->assertFormFieldExists('price_before_discount')
        ->assertFormFieldExists('features')
        ->assertFormFieldExists('comments')
        ->set('data.stockItems')
        ->fillForm([
            'category_id' => $newData->category_id,
            'name'        => $newData->name,
            'stockItems'  => [
                [
                    'color_id' => $size->id,
                    'size_id'  => $color->id,
                    'quantity' => 10,
                ],
            ],
            'slug'                  => $newData->slug,
            'description'           => $newData->description,
            'photos'                => $photos,
            'price'                 => $newData->price,
            'price_before_discount' => $newData->price_before_discount,
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

    $this->assertDatabaseHas('stock_items', [
        'product_id' => Product::query()->where('name', $newData->name)->first()->id,
        'color_id'   => $color->id,
        'size_id'    => $size->id,
        'quantity'   => 10,
    ]);

    $product = Product::query()->where('slug', $newData->slug)->first();

    $this->assertEquals($newData->features, $product->features);
    $this->assertEquals($newData->comments, $product->comments);

    $this->assertCount(3, $product->getMedia());

    $this->assertAuthenticated();
});

it('can validate create input', function () {
    $bigPhoto = UploadedFile::fake()->image('big-photo.jpg')->size(5000);
    $photos = [];

    for ($i = 0; $i < 12; $i++) {
        $photos[] = UploadedFile::fake()->image('photo-' . $i . '.jpg');
    }

    livewire(CreateProduct::class)
        ->fillForm([
            'name'        => null,
            'category_id' => null,
            'description' => null,
            'price'       => null,
            'stockItems'  => [
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
            'name'                  => 'required',
            'category_id'           => 'required',
            'description'           => 'required',
            'price'                 => 'required',
            'stockItems.0.color_id' => 'required',
            'stockItems.0.size_id'  => 'required',
            'stockItems.0.quantity' => 'required',
            'features.0.name'       => 'required',
            'features.0.value'      => 'required',
            'comments.0.comment'    => 'required',
        ])
        ->fillForm([
            'name'        => str_repeat('a', 256),
            'description' => str_repeat('a', 1001),
            'photos'      => $photos,
            'stockItems'  => [
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
            'name'                  => 'max',
            'description'           => 'max',
            'stockItems.0.quantity' => 'max',
            'features.0.name'       => 'max',
            'features.0.value'      => 'max',
            'comments.0.comment'    => 'max',
            'price'                 => 'max',
            'price_before_discount' => 'max',
            'photos'                => 'max',
        ])
        ->fillForm([
            'description' => 'a',
            'stockItems'  => [
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
            'description'           => 'min',
            'stockItems.0.quantity' => 'min',
            'comments.0.comment'    => 'min',
            'price'                 => 'min',
            'price_before_discount' => 'min',
        ])
        ->fillForm([
            'stockItems' => [
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
            'stockItems.0.quantity' => 'integer',
            'features.0.name'       => 'alpha',
            'price'                 => 'integer',
            'price_before_discount' => 'integer',
        ])
        ->fillForm([
            'photos' => [
                $bigPhoto,
            ],
            'price'                 => 100,
            'price_before_discount' => 99,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'photos'                => 'max',
            'price_before_discount' => 'gte',
        ]);

    $this->assertAuthenticated();
});

it('validate photos file type', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->make();

    $video = UploadedFile::fake()->create('video.mp4');

    $component = livewire(CreateProduct::class, [
        'record' => $product->getRouteKey(),
    ])->fillForm([
        'photos' => [
            $video,
        ],
    ])->call('create');

    $this->assertEquals(
        'The fotos field must be a file of type: image/*.',
        $component->errors()->getMessages()['data.photos'][0]
    );
});
