<?php

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Models\Category;

use function Pest\Livewire\livewire;

it('can render edit page', function () {
    $this->get(CategoryResource::getUrl('edit', [
        'record' => Category::factory()->create(),
    ]))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can retrieve data', function () {
    $category = Category::factory()->create();

    livewire(EditCategory::class, [
        'record' => $category->getRouteKey(),
    ])
        ->assertFormSet([
            'name' => $category->name,
            'slug' => $category->slug,
        ]);

    $this->assertAuthenticated();
});

it('can save a category', function () {
    $category = Category::factory()->create();
    $newData = Category::factory()->make();

    livewire(EditCategory::class, [
        'record' => $category->getRouteKey(),
    ])
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('slug')
        ->fillForm([
            'name' => $newData->name,
            'slug' => $newData->slug,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Category::class, [
        'name' => $newData->name,
        'slug' => $newData->slug,
    ]);
});

it('can validate edit input', function () {
    livewire(EditCategory::class, [
        'record' => Category::factory()->create()->getRouteKey(),
    ])
        ->fillForm([
            'name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
        ])
        ->fillForm([
            'name' => str_repeat('a', 256),
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'max',
        ]);

    $this->assertAuthenticated();
});
