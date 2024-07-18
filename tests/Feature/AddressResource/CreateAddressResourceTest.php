<?php

use App\Filament\Resources\AddressResource;
use App\Filament\Resources\AddressResource\Pages\CreateAddress;
use App\Models\Address;

use function Pest\Livewire\livewire;

it('can render create page', function () {
    $address = Address::factory()->create();

    $this->get(AddressResource::getUrl('create', [
        'record' => $address,
    ]))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can create an address', function () {
    $newData = Address::factory()->make();

    livewire(CreateAddress::class)
        ->assertFormExists()
        ->assertFormFieldExists('street_type')
        ->assertFormFieldExists('street_number')
        ->assertFormFieldExists('first_number')
        ->assertFormFieldExists('second_number')
        ->assertFormFieldExists('apartment')
        ->assertFormFieldExists('building')
        ->assertFormFieldExists('full_address')
        ->assertFormFieldExists('phone')
        ->assertFormFieldExists('state_id')
        ->assertFormFieldExists('city_id')
        ->assertFormFieldExists('observation')
        ->fillForm([
            'street_type'   => $newData->street_type->value,
            'street_number' => $newData->street_number,
            'first_number'  => $newData->first_number,
            'second_number' => $newData->second_number,
            'apartment'     => $newData->apartment,
            'building'      => $newData->building,
            'full_address'  => $newData->full_address,
            'phone'         => $newData->phone,
            'state_id'      => $newData->city->state_id,
            'city_id'       => $newData->city_id,
            'observation'   => $newData->observation,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Address::class, [
        'street_type'   => $newData->street_type,
        'street_number' => $newData->street_number,
        'first_number'  => $newData->first_number,
        'second_number' => $newData->second_number,
        'apartment'     => $newData->apartment,
        'building'      => $newData->building,
        'phone'         => $newData->phone,
        'full_address'  => $newData->full_address,
        'city_id'       => $newData->city_id,
        'observation'   => $newData->observation,
    ]);

    $this->assertAuthenticated();
});

it('can validate create input', function () {
    livewire(CreateAddress::class)
        ->fillForm([
            'street_type'   => null,
            'street_number' => null,
            'first_number'  => null,
            'second_number' => null,
            'phone'         => null,
            'city_id'       => null,
            'full_address'  => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'street_type'   => 'required',
            'street_number' => 'required',
            'first_number'  => 'required',
            'second_number' => 'required',
            'phone'         => 'required',
            'city_id'       => 'required',
            'full_address'  => 'required',
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
        ->call('create')
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
