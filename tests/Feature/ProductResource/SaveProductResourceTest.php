<?php

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\StockItem;
use Illuminate\Http\UploadedFile;

use function Pest\Livewire\livewire;

it('can render edit page', function () {
    $category = Category::factory()->create();

    $this->get(ProductResource::getUrl('edit', [
        'record' => Product::factory()->for($category)->create(),
    ]))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can retrieve data', function () {
    $size = Size::factory()->create();
    $color = Color::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();
    $stockItem = StockItem::factory()->for($size)->for($color)->for($product)->create();

    for ($i = 0; $i < 2; $i++) {
        $product->addMedia(UploadedFile::fake()->image('photo-' . $i . '.jpg'))->toMediaCollection();
    }

    livewire(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertFormSet([
            'name'                  => $product->name,
            'stockItems'            => ['record-1' => $stockItem->toArray()],
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
            $product->getMedia()[0]['uuid'],
            $product->getMedia()[1]['uuid'],
        ]);

    $this->assertAuthenticated();
});

it('can save a product', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();
    $size = Size::factory()->create();
    $color = Color::factory()->create();
    $newData = Product::factory()->for($category)->make();

    $photos = [];

    for ($i = 0; $i < 3; $i++) {
        $photos[] = UploadedFile::fake()->image('photo-' . $i . '.jpg');
    }

    livewire(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('category_id')
        ->assertFormFieldExists('stockItems')
        ->assertFormFieldExists('slug')
        ->assertFormFieldExists('description')
        ->assertFormFieldExists('photos')
        ->assertFormFieldExists('price')
        ->assertFormFieldExists('price_before_discount')
        ->set('data.stockItems')
        ->fillForm([
            'category_id' => $newData->category_id,
            'name'        => $newData->name,
            'stockItems'  => [
                [
                    'color_id' => $color->id,
                    'size_id'  => $size->id,
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
        ->call('save')
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

    $this->assertEquals($newData->features[0], $product->features[2]);
    $this->assertEquals($newData->features[1], $product->features[3]);
    $this->assertEquals($newData->comments[0], $product->comments[2]);
    $this->assertEquals($newData->comments[1], $product->comments[3]);

    $this->assertCount(4, $product->getMedia());

    $this->assertAuthenticated();
});

it('can validate edit input', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();
    $bigPhoto = UploadedFile::fake()->image('big-photo.jpg')->size(5000);
    $photos = [];

    for ($i = 0; $i < 12; $i++) {
        $photos[] = UploadedFile::fake()->image('photo-' . $i . '.jpg');
    }

    livewire(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
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
        ->call('save')
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
            'photos'                => $photos,
        ])
        ->call('save')
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
        ->call('save')
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
        ->call('save')
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
        ->call('save')
        ->assertHasFormErrors([
            'price_before_discount' => 'gte',
            'photos'                => 'max',
        ]);

    $this->assertAuthenticated();
});

it('validate photos file type', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    $product->clearMediaCollection();

    $video = UploadedFile::fake()->create('video.mp4');
    $component = livewire(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])->fillForm([
        'photos' => [
            $video,
        ],
    ])->call('save');

    $this->assertEquals(
        'The fotos field must be a file of type: image/*.',
        $component->errors()->getMessages()['data.photos'][0]
    );
});
