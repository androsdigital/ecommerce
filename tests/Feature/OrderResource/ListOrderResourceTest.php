<?php

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;

use function Pest\Livewire\livewire;

it('can render list page', function () {
    $this->get(OrderResource::getUrl())->assertSuccessful();

    $this->assertAuthenticated();
});

it('can list orders', function () {
    $orders = Order::factory(10)->create();

    livewire(ListOrders::class)
        ->assertCanSeeTableRecords($orders)
        ->assertCountTableRecords(10)
        ->assertCanRenderTableColumn('customer.name')
        ->assertCanRenderTableColumn('number')
        ->assertCanRenderTableColumn('status')
        ->assertCanRenderTableColumn('total_price')
        ->assertCanRenderTableColumn('address.full_address')
        ->assertCanNotRenderTableColumn('total_price_before_discount')
        ->assertCanNotRenderTableColumn('total_items_discount')
        ->assertCanNotRenderTableColumn('discount')
        ->assertCanNotRenderTableColumn('total_discount')
        ->assertCanNotRenderTableColumn('total_shipping_price')
        ->assertCanNotRenderTableColumn('total_quantity')
        ->assertCanNotRenderTableColumn('created_at')
        ->assertCanNotRenderTableColumn('updated_at');

    $this->assertAuthenticated();
});

it('can set correct record values', function () {
    $orders = Order::factory(10)->create();

    $order = $orders->random();

    livewire(ListOrders::class)
        ->assertTableColumnStateSet('customer.name', $order->customer->name, record: $order)
        ->assertTableColumnStateSet('number', $order->number, record: $order)
        ->assertTableColumnFormattedStateSet('status', $order->status->getLabel(), record: $order)
        ->assertTableColumnStateSet('total_price', $order->total_price, record: $order)
        ->assertTableColumnStateSet('address.full_address', $order->address->full_address, record: $order)
        ->assertTableColumnStateSet('total_price_before_discount', $order->total_price_before_discount, record: $order)
        ->assertTableColumnStateSet('total_items_discount', $order->total_items_discount, record: $order)
        ->assertTableColumnStateSet('discount', $order->discount, record: $order)
        ->assertTableColumnStateSet('total_discount', $order->total_discount, record: $order)
        ->assertTableColumnStateSet('total_shipping_price', $order->total_shipping_price, record: $order)
        ->assertTableColumnStateSet('total_quantity', $order->total_quantity, record: $order)
        ->assertTableColumnStateSet('created_at', $order->created_at, record: $order)
        ->assertTableColumnStateSet('updated_at', $order->updated_at, record: $order);
});

it('can search orders', function () {
    $orders = Order::factory(10)->create();

    $order = $orders->random();

    livewire(ListOrders::class)
        ->assertCanSeeTableRecords($orders)
        ->searchTable($order->number)
        ->assertCanSeeTableRecords($orders->where('number', $order->number))
        ->assertCountTableRecords($orders->where('number', $order->number)->count())
        ->searchTable($order->address->full_address)
        ->assertCanSeeTableRecords(Order::whereHas('address', function (Builder $query) use ($order) {
            $query->where('full_address', $order->address->full_address);
        })->get())
        ->assertCountTableRecords(Order::whereHas('address', function (Builder $query) use ($order) {
            $query->where('full_address', $order->address->full_address);
        })->get()->count())
        ->searchTable($order->customer->name)
        ->assertCanSeeTableRecords($orders->where('customer_id', $order->customer->id))
        ->assertCountTableRecords($orders->where('customer_id', $order->customer->id)->count());

    $this->assertAuthenticated();
});

it('can sort orders', function () {
    $orders = Order::factory(10)->create();

    livewire(ListOrders::class)
        ->sortTable('costumer.name')
        ->assertCanSeeTableRecords($orders->sortBy('costumer.name'), inOrder: true)
        ->sortTable('costumer.name', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('costumer.name'), inOrder: true)
        ->sortTable('number')
        ->assertCanSeeTableRecords($orders->sortBy('number'), inOrder: true)
        ->sortTable('number', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('number'), inOrder: true)
        ->sortTable('total_price')
        ->assertCanSeeTableRecords($orders->sortBy('total_price'), inOrder: true)
        ->sortTable('total_price', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('total_price'), inOrder: true)
        ->sortTable('total_price_before_discount')
        ->assertCanSeeTableRecords($orders->sortBy('total_price_before_discount'), inOrder: true)
        ->sortTable('total_price_before_discount', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('total_price_before_discount'), inOrder: true)
        ->sortTable('total_items_discount')
        ->assertCanSeeTableRecords($orders->sortBy('total_items_discount'), inOrder: true)
        ->sortTable('total_items_discount', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('total_items_discount'), inOrder: true)
        ->sortTable('discount')
        ->assertCanSeeTableRecords($orders->sortBy('discount'), inOrder: true)
        ->sortTable('discount', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('discount'), inOrder: true)
        ->sortTable('total_discount')
        ->assertCanSeeTableRecords($orders->sortBy('total_discount'), inOrder: true)
        ->sortTable('total_discount', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('total_discount'), inOrder: true)
        ->sortTable('total_shipping_price')
        ->assertCanSeeTableRecords($orders->sortBy('total_shipping_price'), inOrder: true)
        ->sortTable('total_shipping_price', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('total_shipping_price'), inOrder: true)
        ->sortTable('total_quantity')
        ->assertCanSeeTableRecords($orders->sortBy('total_quantity'), inOrder: true)
        ->sortTable('total_quantity', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('total_quantity'), inOrder: true)
        ->sortTable('created_at')
        ->assertCanSeeTableRecords($orders->sortBy('created_at'), inOrder: true)
        ->sortTable('created_at', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('created_at'), inOrder: true)
        ->sortTable('updated_at')
        ->assertCanSeeTableRecords($orders->sortBy('updated_at'), inOrder: true)
        ->sortTable('updated_at', 'desc')
        ->assertCanSeeTableRecords($orders->sortByDesc('updated_at'), inOrder: true);
});

it('can filter by creation date', function () {
    $orders = Order::factory(10)->create();

    $ordersFromMonthAgo = $orders->where('created_at', '>=', now()->subMonth());
    $ordersUntilMonthAgo = $orders->where('created_at', '<', now()->subMonth());

    livewire(ListOrders::class)
        ->assertCanSeeTableRecords($orders)
        ->assertCountTableRecords(10)
        ->filterTable('created_at', [
            'created_from' => now()->subMonth(),
        ])
        ->assertCanSeeTableRecords($ordersFromMonthAgo)
        ->assertCountTableRecords($ordersFromMonthAgo->count())
        ->removeTableFilter('created_at')
        ->filterTable('created_at', [
            'created_until' => now()->subMonth(),
        ])
        ->assertCanSeeTableRecords($ordersUntilMonthAgo)
        ->assertCountTableRecords($ordersUntilMonthAgo->count());
});

it('can sum values in a column', function () {
    $orders = Order::factory(10)->create();

    livewire(ListOrders::class)
        ->assertCanSeeTableRecords($orders)
        ->assertTableColumnSummarySet('total_price', 'sum', $orders->sum('total_price'))
        ->assertTableColumnSummarySet('total_price_before_discount', 'sum', $orders->sum('total_price_before_discount'))
        ->assertTableColumnSummarySet('total_items_discount', 'sum', $orders->sum('total_items_discount'))
        ->assertTableColumnSummarySet('discount', 'sum', $orders->sum('discount'))
        ->assertTableColumnSummarySet('total_discount', 'sum', $orders->sum('total_discount'))
        ->assertTableColumnSummarySet('total_shipping_price', 'sum', $orders->sum('total_shipping_price'))
        ->assertTableColumnSummarySet('total_quantity', 'sum', $orders->sum('total_quantity'));
});

it('can bulk delete orders', function () {
    $orders = Order::factory(10)->create();

    livewire(ListOrders::class)
        ->callTableBulkAction(DeleteBulkAction::class, $orders);

    foreach ($orders as $order) {
        $order->refresh();

        $this->assertNotNull($order->deleted_at);
    }
});
