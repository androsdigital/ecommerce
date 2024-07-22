<?php

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Models\Order;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can render edit page', function () {
    $this->get(OrderResource::getUrl('edit', [
        'record' => Order::factory()->create(),
    ]))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can save a order', function () {
    $order = Order::factory()->create();
    $order->saveOrderItem();

    $newData = Order::factory()->make();

    livewire(EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->assertFormExists()
        ->assertFormFieldExists('customer_id')
        ->assertFormFieldExists('number')
        ->assertFormFieldIsDisabled('number')
        ->assertFormFieldExists('status')
        ->assertFormFieldExists('notes')
        ->assertFormFieldExists('address_id')
        ->fillForm([
            'customer_id' => $newData->customer_id,
            'number'      => $newData->number,
            'status'      => $newData->status,
            'notes'       => $newData->notes,
            'address_id'  => $newData->address_id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Order::class, [
        'customer_id' => $newData->customer_id,
        'number'      => $newData->number,
        'status'      => $newData->status,
        'notes'       => $newData->notes,
        'address_id'  => $newData->address_id,
    ]);

    $this->assertAuthenticated();
});

it('can retrieve data', function () {
    $order = Order::factory()->create();

    livewire(EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->assertFormSet([
            'customer_id' => $order->customer_id,
            'number'      => $order->number,
            'status'      => $order->status->value,
            'notes'       => $order->notes,
            'address_id'  => $order->address_id,
        ]);

    $this->assertAuthenticated();
});

it('can validate save input', function () {
    $order = Order::factory()->create();

    livewire(EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'number'     => null,
            'status'     => null,
            'address_id' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'number'     => 'required',
            'status'     => 'required',
            'address_id' => 'required',
        ])
        ->fillForm([
            'number' => str_repeat('0', 32),
        ])
        ->call('save')
        ->assertHasFormErrors([
            'number' => 'max',
        ]);

    $this->assertAuthenticated();
});

it('can execute address actions correctly', function () {
    $order = Order::factory()->create();

    livewire(EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->assertFormComponentActionExists('address_id', 'createAddress')
        ->assertFormComponentActionEnabled('address_id', 'createAddress')
        ->assertFormComponentActionExists('address_id', 'editAddress')
        ->assertFormComponentActionEnabled('address_id', 'editAddress')
        ->assertFormComponentActionHasUrl(
            'address_id',
            'createAddress',
            route('filament.admin.resources.addresses.create')
        )
        ->assertFormComponentActionHasUrl(
            'address_id',
            'editAddress',
            route('filament.admin.resources.addresses.edit', ['record' => $order->address])
        );

    $this->assertAuthenticated();
});

it('can delete an order', function () {
    $order = Order::factory()->create();

    livewire(EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->callAction(DeleteAction::class)
        ->assertActionHalted(DeleteAction::class);

    $order->refresh();

    $this->assertNotNull($order->deleted_at);

    $this->assertAuthenticated();
});
