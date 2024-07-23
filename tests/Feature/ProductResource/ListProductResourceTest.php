ProductResourceTest.php<?php

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use Filament\Tables\Actions\DeleteBulkAction;

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
        ->assertCanNotRenderTableColumn('created_at')
        ->assertCanNotRenderTableColumn('updated_at');

    $this->assertAuthenticated();
});

it('can set correct record values', function () {
    $products = Product::factory(10)->create();

    $product = $products->random();

    livewire(ListProducts::class)
        ->assertTableColumnStateSet('category.name', $product->category->name, record: $product)
        ->assertTableColumnStateSet('name', $product->name, record: $product)
        ->assertTableColumnStateSet('created_at', $product->created_at, record: $product)
        ->assertTableColumnStateSet('updated_at', $product->updated_at, record: $product);
});

it('can search products', function () {
    $products = Product::factory(10)->create();

    $product = $products->random();

    livewire(ListProducts::class)
        ->assertCanSeeTableRecords($products)
        ->searchTable($product->name)
        ->assertCanSeeTableRecords($products->where('name', $product->name))
        ->assertCountTableRecords($products->where('name', $product->name)->count())
        ->searchTable($product->category->name)
        ->assertCanSeeTableRecords($products->where('category_id', $product->category_id))
        ->assertCountTableRecords($products->where('category_id', $product->category_id)->count());

    $this->assertAuthenticated();
});

it('can sort products', function () {
    $products = Product::factory(10)->create();

    livewire(ListProducts::class)
        ->sortTable('category.name')
        ->assertCanSeeTableRecords($products->sortBy('category.name'), inOrder: true)
        ->sortTable('category.name', 'desc')
        ->assertCanSeeTableRecords($products->sortByDesc('category.name'), inOrder: true)
        ->sortTable('name')
        ->assertCanSeeTableRecords($products->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($products->sortByDesc('name'), inOrder: true)
        ->sortTable('created_at')
        ->assertCanSeeTableRecords($products->sortBy('created_at'), inOrder: true)
        ->sortTable('created_at', 'desc')
        ->assertCanSeeTableRecords($products->sortByDesc('created_at'), inOrder: true)
        ->sortTable('updated_at')
        ->assertCanSeeTableRecords($products->sortBy('updated_at'), inOrder: true)
        ->sortTable('updated_at', 'desc')
        ->assertCanSeeTableRecords($products->sortByDesc('updated_at'), inOrder: true);
});

it('can bulk delete products', function () {
    $products = Product::factory(10)->create();

    livewire(ListProducts::class)
        ->callTableBulkAction(DeleteBulkAction::class, $products);

    foreach ($products as $product) {
        $this->assertModelMissing($product);
    }
});
