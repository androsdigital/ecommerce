<?php

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Models\Customer;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can render edit page', function () {
    $this->get(CustomerResource::getUrl('edit', [
        'record' => Customer::factory()->create(),
    ]))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can save a customer', function () {
    $customer = Customer::factory()->create();
    $newData = Customer::factory()->make();

    livewire(EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('email')
        ->fillForm([
            'name'  => $newData->name,
            'email' => $newData->email,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Customer::class, [
        'name'  => $newData->name,
        'email' => $newData->email,
    ]);

    $this->assertAuthenticated();
});

it('can retrieve data', function () {
    $customer = Customer::factory()->create();

    livewire(EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])
        ->assertFormSet([
            'name'  => $customer->name,
            'email' => $customer->email,
        ]);

    $this->assertAuthenticated();
});

it('can validate save input', function () {
    $customer = Customer::factory()->create();

    livewire(EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])
        ->fillForm([
            'name'  => null,
            'email' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name'  => 'required',
            'email' => 'required',
        ])
        ->fillForm([
            'name'  => str_repeat('0', 256),
            'email' => str_repeat('0', 256),
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name'  => 'max',
            'email' => 'max',
        ])
        ->fillForm([
            'email' => 'no-email',
        ])
        ->call('save')
        ->assertHasFormErrors([
            'email' => 'email',
        ]);

    $this->assertAuthenticated();
});

it('can delete an customer', function () {
    $customer = Customer::factory()->create();

    livewire(EditCustomer::class, [
        'record' => $customer->getRouteKey(),
    ])
        ->callAction(DeleteAction::class)
        ->assertActionHalted(DeleteAction::class);

    $this->assertModelMissing($customer);

    $this->assertAuthenticated();
});
