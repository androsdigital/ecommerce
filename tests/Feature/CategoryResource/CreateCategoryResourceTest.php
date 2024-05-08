<?php

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Models\Category;

use function Pest\Livewire\livewire;

it('can render create page', function () {
    $this->get(CategoryResource::getUrl('create'))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can create a category', function () {
    $newData = Category::factory()->make();

    livewire(CreateCategory::class)
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('slug')
        ->fillForm([
            'name' => $newData->name,
            'slug' => $newData->slug,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Category::class, [
        'name' => $newData->name,
        'slug' => $newData->slug,
    ]);
});

it('can validate create input', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
        ])
        ->fillForm([
            'name' => str_repeat('a', 256),
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'max',
        ]);

    $this->assertAuthenticated();
});
