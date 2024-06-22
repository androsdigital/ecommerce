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
    Size::factory()->create();
    Color::factory()->create();
    $category = Category::factory()->create();
    $newData = Product::factory()->for($category)->create();

    $photos = [];

    for ($i = 0; $i < 3; $i++) {
        $photos[] = UploadedFile::fake()->image('photo-' . $i . '.jpg');
    }

    livewire(CreateProduct::class)
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('category_id')
        ->assertFormFieldExists('slug')
        ->assertFormFieldExists('description')
        ->assertFormFieldExists('photos')
        ->assertFormFieldExists('features')
        ->assertFormFieldExists('comments')
        ->set('data.stockItems')
        ->fillForm([
            'category_id' => $newData->category_id,
            'name'        => $newData->name,
            'slug'        => $newData->slug . '-new',
            'description' => $newData->description,
            'photos'      => $photos,
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

    $this->assertCount(3, $product->getMedia());

    $this->assertAuthenticated();
});

it('can validate create input', function () {
    Size::factory()->create();
    Color::factory()->create();
    Category::factory()->create();
    Product::factory()->create();

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
            'photos'      => $photos,
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
            'photos'             => 'max',
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
        ])
        ->fillForm([
            'photos' => [
                $bigPhoto,
            ],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'photos' => 'max',
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
