<?php

use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\RelationManagers\AddressRelationManager;
use App\Models\Address;
use App\Models\Customer;
use Filament\Tables\Actions\EditAction;

use function Pest\Livewire\livewire;

it('can render addresses edit modal', function () {
    $customer = Customer::factory()->create();

    $address = $customer->addresses->first();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->mountTableAction(EditAction::class, record: $address)
        ->assertTableActionHalted(EditAction::class);

    $this->assertAuthenticated();
});

it('can edit address', function () {
    $customer = Customer::factory()->create();
    $address = $customer->addresses->first();
    $newData = Address::factory()->make();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->callTableAction(EditAction::class, record: $address, data: [
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
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas(Address::class, [
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
    ]);

    $this->assertDatabaseHas('address_customer', [
        'address_id'  => $address->id,
        'customer_id' => $customer->id,
    ]);

    $this->assertAuthenticated();
});

it('can load address data', function () {
    $customer = Customer::factory()->create();
    $address = $customer->addresses->first();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->mountTableAction(EditAction::class, record: $address)
        ->assertTableActionDataSet([
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

it('can validate edit address input', function () {
    $customer = Customer::factory()->create();
    $address = $customer->addresses->first();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->callTableAction(EditAction::class, record: $address, data: [
            'city_id'       => null,
            'street_type'   => null,
            'street_number' => null,
            'first_number'  => null,
            'second_number' => null,
            'full_address'  => null,
            'phone'         => null,
        ])
        ->assertHasTableActionErrors([
            'street_type'   => 'required',
            'street_number' => 'required',
            'first_number'  => 'required',
            'second_number' => 'required',
            'full_address'  => 'required',
            'phone'         => 'required',
            'city_id'       => 'required',
        ])
        ->callTableAction(EditAction::class, record: $address, data: [
            'street_number' => str_repeat('0', 32),
            'first_number'  => str_repeat('0', 32),
            'second_number' => str_repeat('0', 32),
            'phone'         => str_repeat('0', 32),
            'apartment'     => str_repeat('0', 32),
            'building'      => str_repeat('0', 256),
            'full_address'  => str_repeat('0', 256),
        ])
        ->assertHasTableActionErrors([
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
