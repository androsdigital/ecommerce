<?php

use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

use function Pest\Livewire\livewire;

it('can render order items relation manager', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();

    livewire(OrderItemsRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->assertSuccessful();

    $this->assertAuthenticated();
});

it('can list order items in relation manager', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();

    livewire(OrderItemsRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->assertCanSeeTableRecords($order->orderItems)
        ->assertCountTableRecords(10)
        ->assertCanRenderTableColumn('stockItem.product.name')
        ->assertCanRenderTableColumn('stockItem.size.name')
        ->assertCanRenderTableColumn('stockItem.color.name')
        ->assertCanRenderTableColumn('quantity')
        ->assertCanRenderTableColumn('unit_price')
        ->assertCanRenderTableColumn('price')
        ->assertCanNotRenderTableColumn('stockItem.price_before_discount')
        ->assertCanNotRenderTableColumn('stockItem.discount')
        ->assertCanNotRenderTableColumn('shipping_price');

    $this->assertAuthenticated();
});

it('can set correct record values', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();
    $orderItem = $order->orderItems->first();
    $stockItem = $orderItem->stockItem;

    livewire(OrderItemsRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->assertTableColumnStateSet('stockItem.product.name', $stockItem->product->name, record: $orderItem)
        ->assertTableColumnStateSet('stockItem.size.name', $stockItem->size->name, record: $orderItem)
        ->assertTableColumnStateSet('stockItem.color.name', $stockItem->color->name, record: $orderItem)
        ->assertTableColumnStateSet('quantity', $orderItem->quantity, record: $orderItem)
        ->assertTableColumnStateSet('stockItem.price_before_discount', $stockItem->price_before_discount, record: $orderItem)
        ->assertTableColumnStateSet('stockItem.discount', $stockItem->discount, record: $orderItem)
        ->assertTableColumnStateSet('shipping_price', $orderItem->shipping_price, record: $orderItem)
        ->assertTableColumnStateSet(
            'unit_price',
            $stockItem->price_before_discount - $stockItem->discount,
            record: $orderItem
        )
        ->assertTableColumnStateSet(
            'price',
            ($stockItem->price_before_discount - $stockItem->discount + $orderItem->shipping_price) * $orderItem->quantity,
            record: $orderItem
        );
});

it('can sort order items relation manager', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();

    $orderItems = $order->orderItems;

    livewire(OrderItemsRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->sortTable('stockItem.product.name')
        ->assertCanSeeTableRecords($orderItems->sortBy('stockItem.product.name'), inOrder: true)
        ->sortTable('stockItem.product.name', 'desc')
        ->assertCanSeeTableRecords($orderItems->sortByDesc('stockItem.product.name'), inOrder: true)
        ->sortTable('stockItem.size.name')
        ->assertCanSeeTableRecords($orderItems->sortBy('stockItem.size.name'), inOrder: true)
        ->sortTable('stockItem.size.name', 'desc')
        ->assertCanSeeTableRecords($orderItems->sortByDesc('stockItem.size.name'), inOrder: true)
        ->sortTable('stockItem.color.name')
        ->assertCanSeeTableRecords($orderItems->sortBy('stockItem.color.name'), inOrder: true)
        ->sortTable('stockItem.color.name', 'desc')
        ->assertCanSeeTableRecords($orderItems->sortByDesc('stockItem.color.name'), inOrder: true)
        ->sortTable('quantity')
        ->assertCanSeeTableRecords($orderItems->sortBy('quantity'), inOrder: true)
        ->sortTable('quantity', 'desc')
        ->assertCanSeeTableRecords($orderItems->sortByDesc('quantity'), inOrder: true)
        ->sortTable('stockItem.price_before_discount')
        ->assertCanSeeTableRecords($orderItems->sortBy('stockItem.price_before_discount'), inOrder: true)
        ->sortTable('stockItem.price_before_discount', 'desc')
        ->assertCanSeeTableRecords($orderItems->sortByDesc('stockItem.price_before_discount'), inOrder: true)
        ->sortTable('stockItem.discount')
        ->assertCanSeeTableRecords($orderItems->sortBy('stockItem.discount'), inOrder: true)
        ->sortTable('stockItem.discount', 'desc')
        ->assertCanSeeTableRecords($orderItems->sortByDesc('stockItem.discount'), inOrder: true)
        ->sortTable('shipping_price')
        ->assertCanSeeTableRecords($orderItems->sortBy('shipping_price'), inOrder: true)
        ->sortTable('shipping_price', 'desc')
        ->assertCanSeeTableRecords($orderItems->sortByDesc('shipping_price'), inOrder: true);
});

it('can delete orders', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();

    $orderItem = $order->orderItems->first();

    livewire(OrderItemsRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->callTableAction(DeleteAction::class, record: $orderItem);

    $this->assertModelMissing($orderItem);
});

it('can bulk delete orders', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();
    $orderItems = $order->orderItems;

    livewire(OrderItemsRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->callTableBulkAction(DeleteBulkAction::class, $orderItems);

    foreach ($orderItems as $orderItem) {
        $this->assertModelMissing($orderItem);
    }
});
