<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto'),
                TextColumn::make('price')
                    ->label('Precio')
                    ->summarize(Sum::make()->money())
                    ->money(),
                TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
