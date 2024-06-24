<?php

use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemRelationManager;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can render order items edit modal', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();

    $orderItem = $order->orderItems->first();

    livewire(OrderItemRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->mountTableAction(EditAction::class, record: $orderItem)
        ->assertTableActionHalted(EditAction::class);

    $this->assertAuthenticated();
});

it('can edit order item', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();

    $orderItem = $order->orderItems->first();
    $newData = OrderItem::factory()->make();

    livewire(OrderItemRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->callTableAction(EditAction::class, record: $orderItem, data: [
            'quantity'       => $newData->quantity,
            'product_id'     => $newData->stockItem->product_id,
            'size_id'        => $newData->stockItem->size_id,
            'color_id'       => $newData->stockItem->color_id,
            'shipping_price' => $newData->shipping_price,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas(OrderItem::class, [
        'order_id'       => $order->id,
        'stock_item_id'  => $newData->stock_item_id,
        'quantity'       => $newData->quantity,
        'shipping_price' => $newData->shipping_price,
    ]);

    $this->assertAuthenticated();
});

it('can load order item data', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();

    $orderItem = $order->orderItems->first();

    livewire(OrderItemRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->mountTableAction(EditAction::class, record: $orderItem)
        ->assertTableActionDataSet([
            'product_id'     => $orderItem->stockItem->product_id,
            'size_id'        => $orderItem->stockItem->size_id,
            'color_id'       => $orderItem->stockItem->color_id,
            'stock_item_id'  => $orderItem->stock_item_id,
            'quantity'       => $orderItem->quantity,
            'shipping_price' => $orderItem->shipping_price,
        ]);

    $this->assertAuthenticated();
});

it('can validate edit order item input', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();

    $orderItem = $order->orderItems->first();

    livewire(OrderItemRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->callTableAction(EditAction::class, record: $orderItem, data: [
            'product_id'     => null,
            'size_id'        => null,
            'quantity'       => null,
            'shipping_price' => null,
        ])
        ->assertHasTableActionErrors([
            'product_id'     => ['required'],
            'size_id'        => 'required',
            'color_id'       => 'required',
            'quantity'       => 'required',
            'shipping_price' => 'required',
        ])
        ->callTableAction(EditAction::class, record: $orderItem, data: [
            'shipping_price' => -1,
            'quantity'       => 0,
        ])
        ->assertHasTableActionErrors([
            'shipping_price' => 'min',
            'quantity'       => 'min',
        ]);

    $this->assertAuthenticated();
});
