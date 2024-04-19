<?php

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\DeleteAction;

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
        ->assertCanRenderTableColumn('price')
        ->assertCanRenderTableColumn('price_before_discount')
        ->assertCanNotRenderTableColumn('created_at')
        ->assertCanNotRenderTableColumn('updated_at')
        ->searchTable($products->first()->name)
        ->assertCanSeeTableRecords($products->where('name', $products->first()->name))
        ->assertCountTableRecords($products->where('name', $products->first()->name)->count());

    $this->assertAuthenticated();
});

it('can render create page', function () {
    $this->get(ProductResource::getUrl('create'))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can create a product', function () {
    $category = Category::factory()->create();
    $newData = Product::factory()->for($category)->make();

    livewire(CreateProduct::class)
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('category_id')
        ->assertFormFieldExists('slug')
        ->assertFormFieldExists('description')
        ->assertFormFieldExists('photo')
        ->assertFormFieldExists('price')
        ->assertFormFieldExists('price_before_discount')
        ->fillForm([
            'category_id'           => $newData->category_id,
            'name'                  => $newData->name,
            'slug'                  => $newData->slug,
            'description'           => $newData->description,
            'photo'                 => $newData->photo,
            'price'                 => $newData->price / 100,
            'price_before_discount' => $newData->price_before_discount / 100,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Product::class, [
        'category_id'           => $newData->category_id,
        'name'                  => $newData->name,
        'slug'                  => $newData->slug,
        'description'           => $newData->description,
        'price'                 => $newData->price,
        'price_before_discount' => $newData->price_before_discount,
    ]);

    $this->assertAuthenticated();
});

it('can validate create input', function () {
    livewire(CreateProduct::class)
        ->fillForm([
            'name'        => null,
            'category_id' => null,
            'description' => null,
            'price'       => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name'        => 'required',
            'category_id' => 'required',
            'description' => 'required',
            'price'       => 'required',
        ]);

    $this->assertAuthenticated();
});

it('can render edit page', function () {
    $category = Category::factory()->create();

    $this->get(ProductResource::getUrl('edit', [
        'record' => Product::factory()->for($category)->create(),
    ]))->assertSuccessful();

    $this->assertAuthenticated();
});

it('can retrieve data', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    livewire(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertFormSet([
            'name'                  => $product->name,
            'slug'                  => $product->slug,
            'category_id'           => $product->category_id,
            'description'           => $product->description,
            'price'                 => $product->price,
            'price_before_discount' => $product->price_before_discount,
        ]);

    $this->assertAuthenticated();
});

it('can save a product', function () {
    $category = Category::factory()->create();
    $product = Product::factory()
        ->for($category)
        ->create();

    $newData = Product::factory()
        ->for($category)
        ->make();

    livewire(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->assertFormExists()
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('category_id')
        ->assertFormFieldExists('slug')
        ->assertFormFieldExists('description')
        ->assertFormFieldExists('photo')
        ->assertFormFieldExists('price')
        ->assertFormFieldExists('price_before_discount')
        ->fillForm([
            'category_id'           => $newData->category_id,
            'name'                  => $newData->name,
            'slug'                  => $newData->slug,
            'description'           => $newData->description,
            'photo'                 => $newData->photo,
            'price'                 => $newData->price / 100,
            'price_before_discount' => $newData->price_before_discount / 100,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('can validate edit input', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    livewire(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->fillForm([
            'name'        => null,
            'category_id' => null,
            'description' => null,
            'price'       => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name'        => 'required',
            'category_id' => 'required',
            'description' => 'required',
            'price'       => 'required',
        ]);

    $this->assertAuthenticated();
});

it('can delete a product', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    livewire(EditProduct::class, [
        'record' => $product->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($product);

    $this->assertAuthenticated();
});
