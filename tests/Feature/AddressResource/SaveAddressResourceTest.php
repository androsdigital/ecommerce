<?php

use App\Filament\Resources\AddressResource;
use App\Filament\Resources\AddressResource\Pages\EditAddress;
use App\Models\Address;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can render edit page', function () {
    $address = Address::factory()->create();

    $this->get(AddressResource::getUrl('edit', [
        'record' => $address,
    ]))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can save an address', function () {
    $address = Address::factory()->create();

    $newData = Address::factory()->make();

    livewire(EditAddress::class, [
        'record' => $address->getRouteKey(),
    ])
        ->assertFormExists()
        ->assertFormFieldExists('street_type')
        ->assertFormFieldExists('street_number')
        ->assertFormFieldExists('first_number')
        ->assertFormFieldExists('second_number')
        ->assertFormFieldExists('apartment')
        ->assertFormFieldExists('phone')
        ->assertFormFieldExists('state_id')
        ->assertFormFieldExists('city_id')
        ->assertFormFieldExists('observation')
        ->fillForm([
            'street_type'   => $newData->street_type,
            'street_number' => $newData->street_number,
            'first_number'  => $newData->first_number,
            'second_number' => $newData->second_number,
            'apartment'     => $newData->apartment,
            'phone'         => $newData->phone,
            'state_id'      => $newData->state_id,
            'city_id'       => $newData->city_id,
            'observation'   => $newData->observation,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Address::class, [
        'street_type'   => $newData->street_type,
        'street_number' => $newData->street_number,
        'first_number'  => $newData->first_number,
        'second_number' => $newData->second_number,
        'apartment'     => $newData->apartment,
        'phone'         => $newData->phone,
        'city_id'       => $newData->city_id,
        'observation'   => $newData->observation,
    ]);

    $this->assertAuthenticated();
});

it('can validate save input', function () {
    $address = Address::factory()->create();

    livewire(EditAddress::class, [
        'record' => $address->getRouteKey(),
    ])
        ->fillForm([
            'street_type'   => null,
            'street_number' => null,
            'first_number'  => null,
            'second_number' => null,
            'phone'         => null,
            'city_id'       => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'street_type'   => 'required',
            'street_number' => 'required',
            'first_number'  => 'required',
            'second_number' => 'required',
            'phone'         => 'required',
            'city_id'       => 'required',
        ])
        ->fillForm([
            'street_number' => str_repeat('0', 32),
            'first_number'  => str_repeat('0', 32),
            'second_number' => str_repeat('0', 32),
            'phone'         => str_repeat('0', 32),
            'apartment'     => str_repeat('0', 256),
        ])
        ->call('save')
        ->assertHasFormErrors([
            'street_number' => 'max',
            'first_number'  => 'max',
            'second_number' => 'max',
            'phone'         => 'max',
            'apartment'     => 'max',
        ]);

    $this->assertAuthenticated();
});

it('can delete an address', function () {
    $address = Address::factory()->create();

    livewire(EditAddress::class, [
        'record' => $address->getRouteKey(),
    ])
        ->callAction(DeleteAction::class)
        ->assertActionHalted(DeleteAction::class);

    $this->assertModelMissing($address);

    $this->assertAuthenticated();
});
