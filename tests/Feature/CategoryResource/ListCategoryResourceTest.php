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
        ->assertCanNotRenderTableColumn('updated_at');

    $this->assertAuthenticated();
});

it('can set correct record values', function () {
    $categories = Category::factory(10)->create();

    $category = $categories->random();

    livewire(ListCategories::class)
        ->assertTableColumnStateSet('name', $category->name, record: $category)
        ->assertTableColumnStateSet('slug', $category->slug, record: $category)
        ->assertTableColumnStateSet('created_at', $category->created_at, record: $category)
        ->assertTableColumnStateSet('updated_at', $category->updated_at, record: $category);
});

it('can search categories', function () {
    $categories = Category::factory(10)->create();

    $category = $categories->random();

    livewire(ListCategories::class)
        ->assertCanSeeTableRecords($categories)
        ->searchTable($category->name)
        ->assertCanSeeTableRecords($categories->where('name', $category->name))
        ->assertCountTableRecords($categories->where('name', $category->name)->count());

    $this->assertAuthenticated();
});

it('can sort addresses', function () {
    $categories = Category::factory(10)->create();

    livewire(ListCategories::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($categories->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($categories->sortByDesc('name'), inOrder: true)
        ->sortTable('created_at')
        ->assertCanSeeTableRecords($categories->sortBy('created_at'), inOrder: true)
        ->sortTable('created_at', 'desc')
        ->assertCanSeeTableRecords($categories->sortByDesc('created_at'), inOrder: true)
        ->sortTable('updated_at')
        ->assertCanSeeTableRecords($categories->sortBy('updated_at'), inOrder: true)
        ->sortTable('updated_at', 'desc')
        ->assertCanSeeTableRecords($categories->sortByDesc('updated_at'), inOrder: true);
});
