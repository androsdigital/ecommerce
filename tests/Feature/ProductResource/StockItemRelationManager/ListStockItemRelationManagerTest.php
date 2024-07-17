<?php

use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\RelationManagers\StockItemRelationManager;
use App\Models\Product;
use App\Models\StockItem;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

use function Pest\Livewire\livewire;

it('can render stock items', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->assertSuccessful();

    $this->assertAuthenticated();
});

it('can list stock items', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->assertCanSeeTableRecords($product->stockItems)
        ->assertCountTableRecords(10)
        ->assertCanRenderTableColumn('size.name')
        ->assertCanRenderTableColumn('color.name')
        ->assertCanRenderTableColumn('quantity')
        ->assertCanRenderTableColumn('price')
        ->assertCanNotRenderTableColumn('address.full_address')
        ->assertCanNotRenderTableColumn('price_before_discount')
        ->assertCanNotRenderTableColumn('discount');

    $this->assertAuthenticated();
});

it('can set correct record values', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();

    $stockItem = $product->stockItems->first();

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->assertTableColumnStateSet('size.name', $stockItem->size->name, record: $stockItem)
        ->assertTableColumnStateSet('color.name', $stockItem->color->name, record: $stockItem)
        ->assertTableColumnStateSet('address.full_address', $stockItem->address->full_address, record: $stockItem)
        ->assertTableColumnStateSet('quantity', $stockItem->quantity, record: $stockItem)
        ->assertTableColumnStateSet('price_before_discount', $stockItem->price_before_discount, record: $stockItem)
        ->assertTableColumnStateSet('discount', $stockItem->discount, record: $stockItem)
        ->assertTableColumnStateSet(
            'price',
            $stockItem->price_before_discount - $stockItem->discount,
            record: $stockItem
        );
});

it('can sort stock items', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();

    $stockItems = $product->stockItems;

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->sortTable('size.name')
        ->assertCanSeeTableRecords($stockItems->sortBy('size.name'), inOrder: true)
        ->sortTable('size.name', 'desc')
        ->assertCanSeeTableRecords($stockItems->sortByDesc('size.name'), inOrder: true)
        ->sortTable('color.name')
        ->assertCanSeeTableRecords($stockItems->sortBy('color.name'), inOrder: true)
        ->sortTable('color.name', 'desc')
        ->assertCanSeeTableRecords($stockItems->sortByDesc('color.name'), inOrder: true)
        ->sortTable('quantity')
        ->assertCanSeeTableRecords($stockItems->sortBy('quantity'), inOrder: true)
        ->sortTable('quantity', 'desc')
        ->assertCanSeeTableRecords($stockItems->sortByDesc('quantity'), inOrder: true)
        ->sortTable('price_before_discount')
        ->assertCanSeeTableRecords($stockItems->sortBy('price_before_discount'), inOrder: true)
        ->sortTable('price_before_discount', 'desc')
        ->assertCanSeeTableRecords($stockItems->sortByDesc('price_before_discount'), inOrder: true)
        ->sortTable('discount')
        ->assertCanSeeTableRecords($stockItems->sortBy('discount'), inOrder: true)
        ->sortTable('discount', 'desc')
        ->assertCanSeeTableRecords($stockItems->sortByDesc('discount'), inOrder: true)
        ->sortTable('price')
        ->assertCanSeeTableRecords($stockItems->sortBy('price'), inOrder: true)
        ->sortTable('price', 'desc')
        ->assertCanSeeTableRecords($stockItems->sortByDesc('price'), inOrder: true);
});

it('can delete stock item', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();

    $stockItem = $product->stockItems->first();

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->callTableAction(DeleteAction::class, record: $stockItem);

    $this->assertModelMissing($stockItem);
});

it('can bulk delete stock items', function () {
    $product = Product::factory()
        ->has(StockItem::factory()->count(10))
        ->create();
    $stockItems = $product->stockItems;

    livewire(StockItemRelationManager::class, [
        'ownerRecord' => $product,
        'pageClass'   => EditProduct::class,
    ])
        ->callTableBulkAction(DeleteBulkAction::class, $stockItems);

    foreach ($stockItems as $stockItem) {
        $this->assertModelMissing($stockItem);
    }
});
