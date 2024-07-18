<?php

use App\Filament\Resources\AddressResource;
use App\Filament\Resources\AddressResource\Pages\ListAddresses;
use App\Models\Address;
use Filament\Tables\Actions\DeleteBulkAction;

use function Pest\Livewire\livewire;

it('can render list page', function () {
    $this->get(AddressResource::getUrl())->assertSuccessful();

    $this->assertAuthenticated();
});

it('can list addresses', function () {
    $addresses = Address::factory(10)->create();

    livewire(ListAddresses::class)
        ->assertCanSeeTableRecords($addresses)
        ->assertCountTableRecords(10)
        ->assertCanRenderTableColumn('city.state.name')
        ->assertCanRenderTableColumn('city.name')
        ->assertCanRenderTableColumn('full_address')
        ->assertCanRenderTableColumn('building')
        ->assertCanRenderTableColumn('phone')
        ->assertCanRenderTableColumn('location')
        ->assertCanNotRenderTableColumn('created_at')
        ->assertCanNotRenderTableColumn('updated_at');

    $this->assertAuthenticated();
});

it('can set correct record values', function () {
    $addresses = Address::factory(10)->create();

    $address = $addresses->random();

    livewire(ListAddresses::class)
        ->assertTableColumnStateSet('city.state.name', $address->city->state->name, record: $address)
        ->assertTableColumnStateSet('city.name', $address->city->name, record: $address)
        ->assertTableColumnStateSet('full_address', $address->full_address, record: $address)
        ->assertTableColumnStateSet('building', $address->building, record: $address)
        ->assertTableColumnStateSet('phone', $address->phone, record: $address)
        ->assertTableColumnStateSet('location', $address->location, record: $address)
        ->assertTableColumnStateSet('created_at', $address->created_at, record: $address)
        ->assertTableColumnStateSet('updated_at', $address->updated_at, record: $address);
});

it('can search addresses', function () {
    $addresses = Address::factory(10)->create();

    $address = $addresses->random();

    livewire(ListAddresses::class)
        ->assertCanSeeTableRecords($addresses)
        ->searchTable($address->full_address)
        ->assertCanSeeTableRecords($addresses->where('full_address', $address->full_address))
        ->assertCountTableRecords($addresses->where('full_address', $address->full_address)->count())
        ->searchTable($address->building)
        ->assertCanSeeTableRecords($addresses->where('building', $address->building))
        ->assertCountTableRecords($addresses->where('building', $address->building)->count())
        ->searchTable($address->phone)
        ->assertCanSeeTableRecords($addresses->where('phone', $address->phone))
        ->assertCountTableRecords($addresses->where('phone', $address->phone)->count());

    $this->assertAuthenticated();
});

it('can sort addresses', function () {
    $addresses = Address::factory(10)->create();

    livewire(ListAddresses::class)
        ->sortTable('city.state.name')
        ->assertCanSeeTableRecords($addresses->sortBy('city.state.name'), inOrder: true)
        ->sortTable('city.state.name', 'desc')
        ->assertCanSeeTableRecords($addresses->sortByDesc('city.state.name'), inOrder: true)
        ->sortTable('city.name')
        ->assertCanSeeTableRecords($addresses->sortBy('city.name'), inOrder: true)
        ->sortTable('city.name', 'desc')
        ->assertCanSeeTableRecords($addresses->sortByDesc('city.name'), inOrder: true)
        ->sortTable('building')
        ->assertCanSeeTableRecords($addresses->sortBy('building'), inOrder: true)
        ->sortTable('building', 'desc')
        ->assertCanSeeTableRecords($addresses->sortByDesc('building'), inOrder: true)
        ->sortTable('phone')
        ->assertCanSeeTableRecords($addresses->sortBy('phone'), inOrder: true)
        ->sortTable('phone', 'desc')
        ->assertCanSeeTableRecords($addresses->sortByDesc('phone'), inOrder: true)
        ->sortTable('created_at')
        ->assertCanSeeTableRecords($addresses->sortBy('created_at'), inOrder: true)
        ->sortTable('created_at', 'desc')
        ->assertCanSeeTableRecords($addresses->sortByDesc('created_at'), inOrder: true)
        ->sortTable('updated_at')
        ->assertCanSeeTableRecords($addresses->sortBy('updated_at'), inOrder: true)
        ->sortTable('updated_at', 'desc')
        ->assertCanSeeTableRecords($addresses->sortByDesc('updated_at'), inOrder: true);
});

it('can filter by creation date', function () {
    $addresses = Address::factory(10)->create();

    $addressesFromMonthAgo = $addresses->where('created_at', '>', now()->subMonth());
    $addressesUntilMonthAgo = $addresses->where('created_at', '<=', now()->subMonth());

    livewire(ListAddresses::class)
        ->assertCanSeeTableRecords($addresses)
        ->assertCountTableRecords(10)
        ->filterTable('created_at', [
            'created_from' => now()->subMonth(),
        ])
        ->assertCanSeeTableRecords($addressesFromMonthAgo)
        ->assertCountTableRecords($addressesFromMonthAgo->count())
        ->removeTableFilter('created_at')
        ->filterTable('created_at', [
            'created_until' => now()->subMonth(),
        ])
        ->assertCanSeeTableRecords($addressesUntilMonthAgo)
        ->assertCountTableRecords($addressesUntilMonthAgo->count());
});

it('can bulk delete addresses', function () {
    $addresses = Address::factory(10)->create();

    livewire(ListAddresses::class)
        ->callTableBulkAction(DeleteBulkAction::class, $addresses);

    foreach ($addresses as $address) {
        $this->assertModelMissing($address);
    }
});
