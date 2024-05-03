<?php

use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

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
