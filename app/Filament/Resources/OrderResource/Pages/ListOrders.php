<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = OrderResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null         => Tab::make('Todos'),
            'En Proceso' => Tab::make()->query(fn ($query) => $query->where('status', OrderStatus::Processing)),
            'Enviados'   => Tab::make()->query(fn ($query) => $query->where('status', OrderStatus::Shipped)),
            'Entregados' => Tab::make()->query(fn ($query) => $query->where('status', OrderStatus::Delivered)),
            'Cancelados' => Tab::make()->query(fn ($query) => $query->where('status', OrderStatus::Cancelled)),
        ];
    }
}
