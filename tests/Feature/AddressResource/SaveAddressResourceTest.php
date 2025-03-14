<?php

use App\Filament\Resources\AddressResource;
use App\Filament\Resources\AddressResource\Pages\EditAddress;
use App\Models\Address;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can render edit page', function () {
    $this->get(AddressResource::getUrl('edit', [
        'record' => Address::factory()->create(),
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
        ->assertFormFieldExists('state_id')
        ->assertFormFieldExists('city_id')
        ->assertFormFieldExists('street_type')
        ->assertFormFieldExists('street_number')
        ->assertFormFieldExists('first_number')
        ->assertFormFieldExists('second_number')
        ->assertFormFieldExists('apartment')
        ->assertFormFieldExists('building')
        ->assertFormFieldExists('phone')
        ->assertFormFieldExists('full_address')
        ->assertFormFieldExists('observation')
        ->fillForm([
            'state_id'      => $newData->city->state_id,
            'city_id'       => $newData->city_id,
            'street_type'   => $newData->street_type->value,
            'street_number' => $newData->street_number,
            'first_number'  => $newData->first_number,
            'second_number' => $newData->second_number,
            'apartment'     => $newData->apartment,
            'building'      => $newData->building,
            'phone'         => $newData->phone,
            'full_address'  => $newData->full_address,
            'observation'   => $newData->observation,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Address::class, [
        'city_id'       => $newData->city_id,
        'street_type'   => $newData->street_type,
        'street_number' => $newData->street_number,
        'first_number'  => $newData->first_number,
        'second_number' => $newData->second_number,
        'apartment'     => $newData->apartment,
        'building'      => $newData->building,
        'full_address'  => $newData->full_address,
        'phone'         => $newData->phone,
        'observation'   => $newData->observation,
    ]);

    $this->assertAuthenticated();
});

it('can retrieve data', function () {
    $address = Address::factory()->create();

    livewire(EditAddress::class, [
        'record' => $address->getRouteKey(),
    ])
        ->assertFormSet([
            'state_id'      => $address->city->state_id,
            'city_id'       => $address->city_id,
            'street_type'   => $address->street_type->value,
            'street_number' => $address->street_number,
            'first_number'  => $address->first_number,
            'second_number' => $address->second_number,
            'apartment'     => $address->apartment,
            'building'      => $address->building,
            'phone'         => $address->phone,
            'full_address'  => $address->full_address,
            'observation'   => $address->observation,
        ]);

    $this->assertAuthenticated();
});

it('can validate save input', function () {
    $address = Address::factory()->create();

    livewire(EditAddress::class, [
        'record' => $address->getRouteKey(),
    ])
        ->fillForm([
            'city_id'       => null,
            'street_type'   => null,
            'street_number' => null,
            'first_number'  => null,
            'second_number' => null,
            'full_address'  => null,
            'phone'         => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'street_type'   => 'required',
            'street_number' => 'required',
            'first_number'  => 'required',
            'second_number' => 'required',
            'full_address'  => 'required',
            'phone'         => 'required',
            'city_id'       => 'required',
        ])
        ->fillForm([
            'street_number' => str_repeat('0', 32),
            'first_number'  => str_repeat('0', 32),
            'second_number' => str_repeat('0', 32),
            'phone'         => str_repeat('0', 32),
            'apartment'     => str_repeat('0', 32),
            'building'      => str_repeat('0', 256),
            'full_address'  => str_repeat('0', 256),
        ])
        ->call('save')
        ->assertHasFormErrors([
            'street_number' => 'max',
            'first_number'  => 'max',
            'second_number' => 'max',
            'phone'         => 'max',
            'apartment'     => 'max',
            'building'      => 'max',
            'full_address'  => 'max',
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
