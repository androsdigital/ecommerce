<?php

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Models\Customer;
use Filament\Tables\Actions\DeleteBulkAction;

use function Pest\Livewire\livewire;

it('can render list page', function () {
    $this->get(CustomerResource::getUrl())->assertSuccessful();

    $this->assertAuthenticated();
});

it('can list customers', function () {
    $customers = Customer::factory(10)->create();

    livewire(ListCustomers::class)
        ->assertCanSeeTableRecords($customers)
        ->assertCountTableRecords(10)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('email')
        ->assertCanRenderTableColumn('phone')
        ->assertCanNotRenderTableColumn('email_verified_at')
        ->assertCanNotRenderTableColumn('created_at')
        ->assertCanNotRenderTableColumn('updated_at');

    $this->assertAuthenticated();
});

it('can set correct record values', function () {
    $customers = Customer::factory(10)->create();

    $customer = $customers->random();

    livewire(ListCustomers::class)
        ->assertTableColumnStateSet('name', $customer->name, record: $customer)
        ->assertTableColumnStateSet('email', $customer->email, record: $customer)
        ->assertTableColumnStateSet('phone', $customer->phone, record: $customer)
        ->assertTableColumnStateSet('email_verified_at', $customer->email_verified_at, record: $customer)
        ->assertTableColumnStateSet('created_at', $customer->created_at, record: $customer)
        ->assertTableColumnStateSet('updated_at', $customer->updated_at, record: $customer);
});

it('can search customers', function () {
    $customers = Customer::factory(10)->create();

    $customer = $customers->random();

    livewire(ListCustomers::class)
        ->assertCanSeeTableRecords($customers)
        ->searchTable($customer->name)
        ->assertCanSeeTableRecords($customers->where('name', $customer->name))
        ->assertCountTableRecords($customers->where('name', $customer->name)->count())
        ->searchTable($customer->email)
        ->assertCanSeeTableRecords($customers->where('email', $customer->email))
        ->assertCountTableRecords($customers->where('email', $customer->email)->count())
        ->searchTable($customer->phone)
        ->assertCanSeeTableRecords($customers->where('phone', $customer->phone))
        ->assertCountTableRecords($customers->where('phone', $customer->phone)->count());

    $this->assertAuthenticated();
});

it('can sort customers', function () {
    $customers = Customer::factory(10)->create();

    livewire(ListCustomers::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($customers->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($customers->sortByDesc('name'), inOrder: true)
        ->sortTable('email')
        ->assertCanSeeTableRecords($customers->sortBy('email'), inOrder: true)
        ->sortTable('email', 'desc')
        ->assertCanSeeTableRecords($customers->sortByDesc('email'), inOrder: true)
        ->sortTable('phone')
        ->assertCanSeeTableRecords($customers->sortBy('phone'), inOrder: true)
        ->sortTable('phone', 'desc')
        ->assertCanSeeTableRecords($customers->sortByDesc('phone'), inOrder: true)
        ->sortTable('email_verified_at')
        ->assertCanSeeTableRecords($customers->sortBy('email_verified_at'), inOrder: true)
        ->sortTable('email_verified_at', 'desc')
        ->assertCanSeeTableRecords($customers->sortByDesc('email_verified_at'), inOrder: true)
        ->sortTable('created_at')
        ->assertCanSeeTableRecords($customers->sortBy('created_at'), inOrder: true)
        ->sortTable('created_at', 'desc')
        ->assertCanSeeTableRecords($customers->sortByDesc('created_at'), inOrder: true)
        ->sortTable('updated_at')
        ->assertCanSeeTableRecords($customers->sortBy('updated_at'), inOrder: true)
        ->sortTable('updated_at', 'desc')
        ->assertCanSeeTableRecords($customers->sortByDesc('updated_at'), inOrder: true);
});

it('can bulk delete customers', function () {
    $customers = Customer::factory(10)->create();

    livewire(ListCustomers::class)
        ->callTableBulkAction(DeleteBulkAction::class, $customers);

    foreach ($customers as $customer) {
        $this->assertModelMissing($customer);
    }
});
