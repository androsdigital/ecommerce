<?php

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Models\Order;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can render edit page', function () {
    $order = Order::factory()->create();

    $this->get(OrderResource::getUrl('edit', [
        'record' => $order,
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
        ->assertFormFieldExists('address.street_type')
        ->assertFormFieldExists('address.street_number')
        ->assertFormFieldExists('address.first_number')
        ->assertFormFieldExists('address.second_number')
        ->assertFormFieldExists('address.apartment')
        ->assertFormFieldExists('address.phone')
        ->assertFormFieldExists('address.state_id')
        ->assertFormFieldExists('address.city_id')
        ->assertFormFieldExists('address.observation')
        ->fillForm([
            'customer_id'           => $newData->customer_id,
            'number'                => $newData->number,
            'status'                => $newData->status,
            'notes'                 => $newData->notes,
            'address.street_type'   => $newData->address->street_type,
            'address.street_number' => $newData->address->street_number,
            'address.first_number'  => $newData->address->first_number,
            'address.second_number' => $newData->address->second_number,
            'address.apartment'     => $newData->address->apartment_number,
            'address.phone'         => $newData->address->phone,
            'address.state_id'      => $newData->address->state_id,
            'address.city_id'       => $newData->address->city_id,
            'address.observation'   => $newData->address->observation,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Order::class, [
        'customer_id' => $newData->customer_id,
        'number'      => $newData->number,
        'status'      => $newData->status,
        'notes'       => $newData->notes,
        'address_id'  => $newData->address->id,
    ]);

    $this->assertAuthenticated();
});

it('can validate save input', function () {
    $order = Order::factory()->create();

    livewire(EditOrder::class, [
        'record' => $order->getRouteKey(),
    ])
        ->fillForm([
            'number'                => null,
            'status'                => null,
            'address.street_type'   => null,
            'address.street_number' => null,
            'address.first_number'  => null,
            'address.second_number' => null,
            'address.phone'         => null,
            'address.city_id'       => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'number'                => 'required',
            'status'                => 'required',
            'address.street_type'   => 'required',
            'address.street_number' => 'required',
            'address.first_number'  => 'required',
            'address.second_number' => 'required',
            'address.phone'         => 'required',
            'address.city_id'       => 'required',
        ])
        ->fillForm([
            'number'                => str_repeat('0', 32),
            'address.street_number' => str_repeat('0', 32),
            'address.first_number'  => str_repeat('0', 32),
            'address.second_number' => str_repeat('0', 32),
            'address.phone'         => str_repeat('0', 32),
            'address.apartment'     => str_repeat('0', 256),
        ])
        ->call('save')
        ->assertHasFormErrors([
            'number'                => 'max',
            'address.street_number' => 'max',
            'address.first_number'  => 'max',
            'address.second_number' => 'max',
            'address.phone'         => 'max',
            'address.apartment'     => 'max',
        ]);

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
