<?php

use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\RelationManagers\StockItemRelationManager;
use App\Models\Product;
use App\Models\StockItem;
use Filament\Tables\Actions\EditAction;
use Illuminate\Http\UploadedFile;

use function Pest\Livewire\livewire;

it('can render stock items edit modal', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();

    $stockItem = $product->stockItems->first();

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->mountTableAction(EditAction::class, record: $stockItem)
        ->assertTableActionHalted(EditAction::class);

    $this->assertAuthenticated();
});

it('can edit stock item', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();

    $stockItem = $product->stockItems->first();
    $newData = StockItem::factory()->make();

    $photos = [];

    for ($i = 0; $i < 3; $i++) {
        $photos[] = UploadedFile::fake()->image('photo-' . $i . '.png');
    }

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->callTableAction(EditAction::class, record: $stockItem, data: [
            'quantity'              => $newData->quantity,
            'size_id'               => $newData->size_id,
            'color_id'              => $newData->color_id,
            'price_before_discount' => $newData->price_before_discount,
            'discount'              => $newData->discount,
            'address_id'            => $newData->address_id,
            'photos'                => $photos,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas(StockItem::class, [
        'product_id'            => $product->id,
        'quantity'              => $newData->quantity,
        'size_id'               => $newData->size_id,
        'color_id'              => $newData->color_id,
        'address_id'            => $newData->address_id,
        'price'                 => $newData->price,
        'price_before_discount' => $newData->price_before_discount,
        'discount'              => $newData->discount,
    ]);

    $this->assertDatabaseHas('media', [
        'model_type' => StockItem::class,
        'model_id'   => $stockItem->id,
        'mime_type'  => 'image/png',
    ]);

    $this->assertAuthenticated();
});

it('can load stock item data', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();

    $stockItem = $product->stockItems->first();

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->mountTableAction(EditAction::class, record: $stockItem)
        ->assertTableActionDataSet([
            'size_id'               => $stockItem->size_id,
            'color_id'              => $stockItem->color_id,
            'quantity'              => $stockItem->quantity,
            'price'                 => $stockItem->price,
            'price_before_discount' => $stockItem->price_before_discount,
            'discount'              => $stockItem->discount,
            'address_id'            => $stockItem->address_id,
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
        ->callTableAction(EditAction::class, record: $stockItem, data: [
            'size_id'               => null,
            'color_id'              => null,
            'quantity'              => null,
            'price_before_discount' => null,
            'discount'              => null,
            'address_id'            => null,
        ])
        ->assertHasTableActionErrors([
            'size_id'               => 'required',
            'color_id'              => 'required',
            'quantity'              => 'required',
            'price_before_discount' => 'required',
            'discount'              => 'required',
            'address_id'            => 'required',
        ])
        ->callTableAction(EditAction::class, record: $stockItem, data: [
            'quantity'              => -1,
            'price_before_discount' => -1,
            'discount'              => -1,
        ])
        ->assertHasTableActionErrors([
            'quantity'              => 'min',
            'price_before_discount' => 'min',
            'discount'              => 'min',
        ])
        ->callTableAction(EditAction::class, record: $stockItem, data: [
            'price_before_discount' => 1000,
            'discount'              => 2000,
            'photos'                => $photos,
        ])
        ->assertHasTableActionErrors([
            'discount' => 'max',
            'photos'   => 'max',
        ])
        ->callTableAction(EditAction::class, record: $stockItem, data: [
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
        ->callTableAction(EditAction::class, record: $stockItem, data: [
            'photos' => [
                $video,
            ],
        ]);

    $this->assertEquals(
        'The fotos field must be a file of type: image/*.',
        $component->errors()->getMessages()['mountedTableActionsData.0.photos'][0]
    );
});
