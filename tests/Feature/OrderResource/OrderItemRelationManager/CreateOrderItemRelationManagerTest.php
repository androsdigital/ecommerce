
<?php

use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemRelationManager;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Tables\Actions\CreateAction;

use function Pest\Livewire\livewire;

it('can render order items create modal', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();

    livewire(OrderItemRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->callTableAction(CreateAction::class)
        ->assertTableActionHalted(CreateAction::class);

    $this->assertAuthenticated();
});

it('can create order item', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(2))
        ->create();

    $newData = OrderItem::factory()->make();

    livewire(OrderItemRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->callTableAction(CreateAction::class, data: [
            'product_id'     => $newData->stockItem->product_id,
            'size_id'        => $newData->stockItem->size_id,
            'color_id'       => $newData->stockItem->color_id,
            'shipping_price' => $newData->shipping_price,
            'quantity'       => $newData->quantity,
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

it('can validate create order item input', function () {
    $order = Order::factory()
        ->has(OrderItem::factory()->count(10))
        ->create();

    livewire(OrderItemRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass'   => EditOrder::class,
    ])
        ->callTableAction(CreateAction::class, data: [
            'product_id'     => null,
            'size_id'        => null,
            'quantity'       => null,
            'shipping_price' => null,
        ])
        ->assertHasTableActionErrors([
            'product_id'     => 'required',
            'size_id'        => 'required',
            'color_id'       => 'required',
            'quantity'       => 'required',
            'shipping_price' => 'required',
        ])
        ->callTableAction(CreateAction::class, data: [
            'shipping_price' => -1,
            'quantity'       => 0,
        ])
        ->assertHasTableActionErrors([
            'shipping_price' => 'min',
            'quantity'       => 'min',
        ]);

    $this->assertAuthenticated();
});
