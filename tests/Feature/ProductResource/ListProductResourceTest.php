ProductResourceTest.php<?php

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;

use function Pest\Livewire\livewire;

it('can render list page', function () {
    $this->get(ProductResource::getUrl())->assertSuccessful();

    $this->assertAuthenticated();
});

it('can list products', function () {
    $category = Category::factory()->create();
    $products = Product::factory()
        ->count(10)
        ->for($category)
        ->create();

    livewire(ListProducts::class)
        ->assertCanSeeTableRecords($products)
        ->assertCountTableRecords(10)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('category.name')
        ->assertCanRenderTableColumn('price_before_discount')
        ->assertCanRenderTableColumn('quantity')
        ->assertCanNotRenderTableColumn('created_at')
        ->assertCanNotRenderTableColumn('updated_at')
        ->searchTable($products->first()->name)
        ->assertCanSeeTableRecords($products->where('name', $products->first()->name))
        ->assertCountTableRecords($products->where('name', $products->first()->name)->count())
        ->searchTable($products->first()->category->name)
        ->assertCanSeeTableRecords($products->where('category_id', $products->first()->category_id))
        ->assertCountTableRecords($products->where('category_id', $products->first()->category_id)->count());

    $this->assertAuthenticated();
});
