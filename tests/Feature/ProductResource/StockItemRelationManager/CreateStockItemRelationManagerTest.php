<?php

use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\RelationManagers\StockItemRelationManager;
use App\Models\Product;
use App\Models\StockItem;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Http\UploadedFile;

use function Pest\Livewire\livewire;

it('can render stock items create modal', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->mountTableAction(CreateAction::class)
        ->assertTableActionHalted(CreateAction::class);

    $this->assertAuthenticated();
});

it('can create stock item', function () {
    $product = Product::factory()->create();

    $newData = StockItem::factory()->make();

    $photos = [];

    for ($i = 0; $i < 3; $i++) {
        $photos[] = UploadedFile::fake()->image('photo-' . $i . '.png');
    }

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->callTableAction(CreateAction::class, data: [
            'quantity'              => $newData->quantity,
            'size_id'               => $newData->size_id,
            'color_id'              => $newData->color_id,
            'price_before_discount' => $newData->price_before_discount,
            'discount'              => $newData->discount,
            'address.street_type'   => $newData->address->street_type,
            'address.street_number' => $newData->address->street_number,
            'address.first_number'  => $newData->address->first_number,
            'address.second_number' => $newData->address->second_number,
            'address.apartment'     => $newData->address->apartment_number,
            'address.phone'         => $newData->address->phone,
            'address.state_id'      => $newData->address->state_id,
            'address.city_id'       => $newData->address->city_id,
            'address.observation'   => $newData->address->observation,
            'photos'                => $photos,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas(StockItem::class, [
        'product_id'            => $product->id,
        'quantity'              => $newData->quantity,
        'size_id'               => $newData->size_id,
        'color_id'              => $newData->color_id,
        'price'                 => $newData->price,
        'price_before_discount' => $newData->price_before_discount,
        'discount'              => $newData->discount,
    ]);

    $this->assertDatabaseHas('media', [
        'model_type' => StockItem::class,
        'model_id'   => $product->stockItems->first()->id,
        'mime_type'  => 'image/png',
    ]);

    $this->assertAuthenticated();
});

it('can validate edit stock item input', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();

    $stockItem = $product->stockItems->first();

    $bigPhoto = UploadedFile::fake()->image('big-photo.jpg')->size(5000);
    $photos = [];

    for ($i = 0; $i < 12; $i++) {
        $photos[] = UploadedFile::fake()->image('photo-' . $i . '.jpg');
    }

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->callTableAction(CreateAction::class, record: $stockItem, data: [
            'size_id'               => null,
            'color_id'              => null,
            'quantity'              => null,
            'price_before_discount' => null,
            'discount'              => null,
            'address.street_type'   => null,
            'address.street_number' => null,
            'address.first_number'  => null,
            'address.second_number' => null,
            'address.phone'         => null,
            'address.city_id'       => null,
        ])
        ->assertHasTableActionErrors([
            'size_id'               => 'required',
            'color_id'              => 'required',
            'quantity'              => 'required',
            'price_before_discount' => 'required',
            'discount'              => 'required',
            'address.street_type'   => 'required',
            'address.street_number' => 'required',
            'address.first_number'  => 'required',
            'address.second_number' => 'required',
            'address.phone'         => 'required',
            'address.city_id'       => 'required',
        ])
        ->callTableAction(CreateAction::class, record: $stockItem, data: [
            'quantity'              => -1,
            'price_before_discount' => -1,
            'discount'              => -1,
        ])
        ->assertHasTableActionErrors([
            'quantity'              => 'min',
            'price_before_discount' => 'min',
            'discount'              => 'min',
        ])
        ->callTableAction(CreateAction::class, record: $stockItem, data: [
            'price_before_discount' => 1000,
            'discount'              => 2000,
            'address.street_number' => str_repeat('0', 32),
            'address.first_number'  => str_repeat('0', 32),
            'address.second_number' => str_repeat('0', 32),
            'address.phone'         => str_repeat('0', 32),
            'address.apartment'     => str_repeat('0', 256),
            'photos'                => $photos,
        ])
        ->assertHasTableActionErrors([
            'discount'              => 'max',
            'address.street_number' => 'max',
            'address.first_number'  => 'max',
            'address.second_number' => 'max',
            'address.phone'         => 'max',
            'address.apartment'     => 'max',
            'photos'                => 'max',
        ])
        ->callTableAction(CreateAction::class, record: $stockItem, data: [
            'photos' => [
                $bigPhoto,
            ],
        ])
        ->assertHasTableActionErrors([
            'photos' => 'max',
        ]);

    $this->assertAuthenticated();
});

it('validate photos file type', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();

    $stockItem = $product->stockItems->first();
    $video = UploadedFile::fake()->create('video.mp4');

    $component = livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->callTableAction(CreateAction::class, record: $stockItem, data: [
            'photos' => [
                $video,
            ],
        ]);

    $this->assertEquals(
        'The fotos field must be a file of type: image/*.',
        $component->errors()->getMessages()['mountedTableActionsData.0.photos'][0]
    );
});
