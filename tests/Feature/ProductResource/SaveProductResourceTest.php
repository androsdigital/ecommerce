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
    Size::factory()->create();
    Color::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();
    $stockItem = StockItem::factory()->create();

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
    Size::factory()->create();
    Color::factory()->create();
    $stockItem = StockItem::factory()->make();
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
        ->assertFormFieldExists('slug')
        ->assertFormFieldExists('description')
        ->assertFormFieldExists('photos')
        ->fillForm([
            'category_id' => $newData->category_id,
            'name'        => $newData->name,
            'slug'        => $newData->slug,
            'description' => $newData->description,
            'photos'      => $photos,
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

it('can validate edit input', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();
    Size::factory()->create();
    Color::factory()->create();

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
            'photos' => $photos,
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
        ])
        ->fillForm([
            'photos' => [
                $bigPhoto,
            ],
        ])
        ->call('save')
        ->assertHasFormErrors([
            'photos' => 'max',
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
