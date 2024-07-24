<?php

use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\RelationManagers\AddressRelationManager;
use App\Models\Address;
use App\Models\Customer;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\DetachBulkAction;

use function Pest\Livewire\livewire;

it('can render addresses', function () {
    $customer = Customer::factory()->create();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->assertSuccessful();

    $this->assertAuthenticated();
});

it('can list addresses', function () {
    $customer = Customer::factory()->create();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->assertCanSeeTableRecords($customer->addresses)
        ->assertCountTableRecords(3)
        ->assertCanRenderTableColumn('full_address')
        ->assertCanRenderTableColumn('building')
        ->assertCanRenderTableColumn('phone');

    $this->assertAuthenticated();
});

it('can set correct record values', function () {
    $customer = Customer::factory()->create();
    $address = $customer->addresses->random();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->assertTableColumnStateSet('full_address', $address->full_address, record: $address)
        ->assertTableColumnStateSet('building', $address->building, record: $address)
        ->assertTableColumnStateSet('phone', $address->phone, record: $address);
});

it('can sort addresses', function () {
    $customer = Customer::factory()->create();
    $addresses = $customer->addresses;

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->sortTable('full_address')
        ->assertCanSeeTableRecords($addresses->sortBy('full_address'), inOrder: true)
        ->sortTable('full_address', 'desc')
        ->assertCanSeeTableRecords($addresses->sortByDesc('full_address'), inOrder: true)
        ->sortTable('building')
        ->assertCanSeeTableRecords($addresses->sortBy('building'), inOrder: true)
        ->sortTable('building', 'desc')
        ->assertCanSeeTableRecords($addresses->sortByDesc('building'), inOrder: true)
        ->sortTable('phone')
        ->assertCanSeeTableRecords($addresses->sortBy('phone'), inOrder: true)
        ->sortTable('phone', 'desc')
        ->assertCanSeeTableRecords($addresses->sortByDesc('phone'), inOrder: true);
});

it('can attach an address', function () {
    $customer = Customer::factory()->create();
    $address = Address::factory()->create();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->callTableAction(AttachAction::class, data: [
            'recordId' => $address->id,
        ]);

    $this->assertNotNull($customer->addresses()->find($address->id));
});

it('can detach address', function () {
    $customer = Customer::factory()->create();
    $address = $customer->addresses->first();

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->callTableAction(DetachAction::class, record: $address);

    $this->assertNull($customer->addresses()->find($address->id));
});

it('can bulk detach addresses', function () {
    $customer = Customer::factory()->create();
    $addresses = $customer->addresses;

    livewire(AddressRelationManager::class, [
        'ownerRecord' => $customer,
        'pageClass'   => EditCustomer::class,
    ])
        ->callTableBulkAction(DetachBulkAction::class, $addresses);

    foreach ($addresses as $address) {
        $this->assertNull($customer->addresses()->find($address->id));
    }
});
