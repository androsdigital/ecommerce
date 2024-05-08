<?php

use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Models\Category;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can delete a product', function () {
    $category = Category::factory()->create();

    livewire(EditCategory::class, [
        'record' => $category->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($category);

    $this->assertAuthenticated();
});
