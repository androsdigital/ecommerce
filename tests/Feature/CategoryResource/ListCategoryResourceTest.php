CategoryResourceTest.php<?php

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;

use function Pest\Livewire\livewire;

it('can render list page', function () {
    $this->get(CategoryResource::getUrl())->assertSuccessful();

    $this->assertAuthenticated();
});

it('can list products', function () {
    $categories = Category::factory()->count(10)->create();

    livewire(ListCategories::class)
        ->assertCanSeeTableRecords($categories)
        ->assertCountTableRecords(10)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('slug')
        ->assertCanNotRenderTableColumn('created_at')
        ->assertCanNotRenderTableColumn('updated_at')
        ->searchTable($categories->first()->name)
        ->assertCanSeeTableRecords($categories->where('name', $categories->first()->name))
        ->assertCountTableRecords($categories->where('name', $categories->first()->name)->count());

    $this->assertAuthenticated();
});
