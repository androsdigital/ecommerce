<?php

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Livewire\livewire;

it('can render create page', function () {
    $this->get(UserResource::getUrl('create'))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can create an user', function () {
    $newData = User::factory()->make();

    livewire(CreateUser::class)
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('email')
        ->fillForm([
            'name'                  => $newData->name,
            'email'                 => $newData->email,
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(User::class, [
        'name'  => $newData->name,
        'email' => $newData->email,
    ]);

    $this->assertTrue(Hash::check('password', $newData->password));

    $this->assertAuthenticated();
});

it('can validate create input', function () {
    livewire(CreateUser::class)
        ->fillForm([
            'name'     => null,
            'email'    => null,
            'password' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name'     => 'required',
            'email'    => 'required',
            'password' => 'required',
        ])
        ->fillForm([
            'name'     => str_repeat('0', 256),
            'email'    => str_repeat('0', 256),
            'password' => str_repeat('0', 256),
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name'                  => 'max',
            'email'                 => 'max',
            'password'              => 'max',
            'password_confirmation' => 'required',
        ])
        ->fillForm([
            'email'                 => 'no-email',
            'password'              => 'password',
            'password_confirmation' => 'no-password',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'email'                 => 'email',
            'password_confirmation' => 'same',
        ]);

    $this->assertAuthenticated();
});
