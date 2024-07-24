<?php

use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\RelationManagers\AddressRelationManager;
use App\Models\Address;
use App\Models\Customer;
use Filament\Tables\Actions\CreateAction;

use function Pest\Livewire\livewire;

it('can render addresses create modal', function () {
    $customer = Customer::factory()->create();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->mountTableAction(CreateAction::class)
        ->assertTableActionHalted(CreateAction::class);

    $this->assertAuthenticated();
});

it('can create address', function () {
    $customer = Customer::factory()->create();
    $newData = Address::factory()->make();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->callTableAction(CreateAction::class, data: [
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

    $address = Address::where('full_address', $newData->full_address)->first();

    $this->assertDatabaseHas('address_customer', [
        'address_id'  => $address->id,
        'customer_id' => $customer->id,
    ]);

    $this->assertAuthenticated();
});

it('can validate create address input', function () {
    $customer = Customer::factory()->create();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->callTableAction(CreateAction::class, data: [
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
        ->callTableAction(CreateAction::class, data: [
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
