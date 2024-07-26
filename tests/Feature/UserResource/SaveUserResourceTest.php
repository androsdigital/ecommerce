<?php

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Hash;

use function Pest\Livewire\livewire;

it('can render edit page', function () {
    $this->get(UserResource::getUrl('edit', [
        'record' => User::factory()->create(),
    ]))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can save an user', function () {
    $user = User::factory()->create();
    $newData = User::factory()->make();

    livewire(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('email')
        ->fillForm([
            'name'                  => $newData->name,
            'email'                 => $newData->email,
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(User::class, [
        'name'  => $newData->name,
        'email' => $newData->email,
    ]);

    $this->assertTrue(Hash::check('password', $user->fresh()->password));

    $this->assertAuthenticated();
});

it('can retrieve data', function () {
    $user = User::factory()->create();

    livewire(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->assertFormSet([
            'name'  => $user->name,
            'email' => $user->email,
        ]);

    $this->assertAuthenticated();
});

it('can validate save input', function () {
    $user = User::factory()->create();

    livewire(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->fillForm([
            'name'     => null,
            'email'    => null,
            'password' => 'password',
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name'                  => 'required',
            'email'                 => 'required',
            'password_confirmation' => 'required',
        ])
        ->fillForm([
            'name'     => str_repeat('0', 256),
            'email'    => str_repeat('0', 256),
            'password' => str_repeat('0', 256),
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name'     => 'max',
            'email'    => 'max',
            'password' => 'max',
        ])
        ->fillForm([
            'email'                 => 'no-email',
            'password'              => 'password',
            'password_confirmation' => 'no-password',
        ])
        ->call('save')
        ->assertHasFormErrors([
            'email'                 => 'email',
            'password_confirmation' => 'same',
        ]);

    $this->assertAuthenticated();
});

it('can delete an user', function () {
    $user = User::factory()->create();

    livewire(EditUser::class, [
        'record' => $user->getRouteKey(),
    ])
        ->callAction(DeleteAction::class)
        ->assertActionHalted(DeleteAction::class);

    $this->assertModelMissing($user);

    $this->assertAuthenticated();
});
