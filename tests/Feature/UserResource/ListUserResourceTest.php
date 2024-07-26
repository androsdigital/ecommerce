<?php

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Tables\Actions\DeleteBulkAction;

use function Pest\Livewire\livewire;

it('can render list page', function () {
    $this->get(UserResource::getUrl())->assertSuccessful();

    $this->assertAuthenticated();
});

it('can list users', function () {
    $users = User::factory(9)->create();

    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users->add($this->user))
        ->assertCountTableRecords(10)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('email')
        ->assertCanNotRenderTableColumn('email_verified_at')
        ->assertCanNotRenderTableColumn('created_at')
        ->assertCanNotRenderTableColumn('updated_at');

    $this->assertAuthenticated();
});

it('can set correct record values', function () {
    $users = User::factory(10)->create();

    $user = $users->random();

    livewire(ListUsers::class)
        ->assertTableColumnStateSet('name', $user->name, record: $user)
        ->assertTableColumnStateSet('email', $user->email, record: $user)
        ->assertTableColumnStateSet('email_verified_at', $user->email_verified_at, record: $user)
        ->assertTableColumnStateSet('created_at', $user->created_at, record: $user)
        ->assertTableColumnStateSet('updated_at', $user->updated_at, record: $user);
});

it('can search users', function () {
    $users = User::factory(9)->create();
    $users->add($this->user);
    $user = $users->random();

    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->searchTable($user->name)
        ->assertCanSeeTableRecords($users->where('name', $user->name))
        ->assertCountTableRecords($users->where('name', $user->name)->count())
        ->searchTable($user->email)
        ->assertCanSeeTableRecords($users->where('email', $user->email))
        ->assertCountTableRecords($users->where('email', $user->email)->count());

    $this->assertAuthenticated();
});

//it('can sort users', function () {
//    $users = User::factory(9)->create();
//    $users->add($this->user);
//
//    livewire(ListUsers::class)
//        ->assertCanSeeTableRecords($users)
//        ->sortTable('name')
//        ->assertCanSeeTableRecords($users->sortBy('name'), inOrder: true)
//        ->sortTable('name', 'desc')
//        ->assertCanSeeTableRecords($users->sortByDesc('name'), inOrder: true)
//        ->sortTable('email')
//        ->assertCanSeeTableRecords($users->sortBy('email'), inOrder: true)
//        ->sortTable('email', 'desc')
//        ->assertCanSeeTableRecords($users->sortByDesc('email'), inOrder: true);
//        ->sortTable('email_verified_at')
//        ->assertCanSeeTableRecords($users->sortBy('email_verified_at'), inOrder: true)
//        ->sortTable('email_verified_at', 'desc')
//        ->assertCanSeeTableRecords($users->sortByDesc('email_verified_at'), inOrder: true)
//        ->sortTable('created_at')
//        ->assertCanSeeTableRecords($users->sortBy('created_at'), inOrder: true)
//        ->sortTable('created_at', 'desc')
//        ->assertCanSeeTableRecords($users->sortByDesc('created_at'), inOrder: true)
//        ->sortTable('updated_at')
//        ->assertCanSeeTableRecords($users->sortBy('updated_at'), inOrder: true)
//        ->sortTable('updated_at', 'desc')
//        ->assertCanSeeTableRecords($users->sortByDesc('updated_at'), inOrder: true);
//});

it('can bulk delete users', function () {
    $users = User::factory(10)->create();

    livewire(ListUsers::class)
        ->callTableBulkAction(DeleteBulkAction::class, $users);

    foreach ($users as $user) {
        $this->assertModelMissing($user);
    }
});
