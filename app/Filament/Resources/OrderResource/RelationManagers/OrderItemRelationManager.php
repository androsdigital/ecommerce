<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Color;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Size;
use App\Models\StockItem;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderItemRelationManager extends RelationManager
{
    protected static ?string $title = 'Items del Pedido';

    protected static ?string $modelLabel = 'item';

    protected static string $relationship = 'orderItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(5)
                    ->schema($this->getStockItemFormSchema()),

                Section::make()
                    ->columns(4)
                    ->schema($this->getPricingFormSchema()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stockItem.product.name')
                    ->label('Producto')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stockItem.size.name')
                    ->label('Talla')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stockItem.color.name')
                    ->label('Color')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(locale: 'es')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('unit_price')
                    ->label('Precio Unitario')
                    ->numeric(locale: 'es')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('price')
                    ->label('Total con envío')
                    ->numeric(locale: 'es')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('shipping_price')
                    ->label('Costo de envío')
                    ->numeric(locale: 'es')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('stockItem.price_before_discount')
                    ->label('Precio antes del descuento')
                    ->numeric(locale: 'es')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('stockItem.discount')
                    ->label('Descuento')
                    ->numeric(locale: 'es')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make('edit'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    protected function getStockItemFormSchema(): array
    {
        return [
            Select::make('product_id')
                ->columnSpan(2)
                ->label('Producto')
                ->live()
                ->required()
                ->searchable()
                ->dehydrated(false)
                ->native(false)
                ->options(Product::pluck('name', 'id'))
                ->afterStateUpdated(function (Set $set) {
                    $set('size_id', '');
                    $set('color_id', '');
                }),

            Select::make('size_id')
                ->label('Talla')
                ->live()
                ->required()
                ->searchable()
                ->dehydrated(false)
                ->native(false)
                ->options(function (Get $get) {
                    return Size::whereHas('stockItems', function (Builder $query) use ($get) {
                        $query->where('product_id', $get('product_id'));
                    })->pluck('name', 'id');
                })
                ->afterStateUpdated(function (Set $set) {
                    $set('color_id', '');
                }),

            Select::make('color_id')
                ->searchable()
                ->live()
                ->required()
                ->label('Color')
                ->dehydrated(false)
                ->native(false)
                ->options(function (?OrderItem $record, Get $get, Set $set) {
                    if (! is_null($record) && $get('product_id') === null && $get('size_id') === null && $get('color_id') === null) {
                        $product_id = $record->stockItem->product->id;
                        $size_id = $record->stockItem->size->id;
                        $color_id = $record->stockItem->color->id;

                        $set('product_id', $product_id);
                        $set('size_id', $size_id);
                        $set('color_id', $color_id);
                    }

                    return Color::whereHas('stockItems', function (Builder $query) use ($get) {
                        $query->where('product_id', $get('product_id'))
                            ->where('size_id', $get('size_id'));
                    })->pluck('name', 'id');
                })
                ->afterStateUpdated(function (Set $set, Get $get) {
                    if ($get('product_id') === null
                        || $get('size_id') === null
                        || $get('color_id') === null) {
                        return;
                    }

                    $stockItem = StockItem::where('product_id', $get('product_id'))
                        ->where('size_id', $get('size_id'))
                        ->where('color_id', $get('color_id'))
                        ->first();

                    $set('unit_price', $stockItem->price);

                    $set('price', function (Get $get): int {
                        return ((int) $get('unit_price') * (int) $get('quantity')) + (int) $get('shipping_price');
                    });

                    $set('stock_item_id', $stockItem->id);
                }),

            TextInput::make('stock_item_id')
                ->label('Stock Item ID')
                ->disabled()
                ->dehydrated(),
        ];
    }

    protected function getPricingFormSchema(): array
    {
        return [
            TextInput::make('shipping_price')
                ->default(0)
                ->label('Costo de envío')
                ->live()
                ->numeric()
                ->required()
                ->minValue(0)
                ->afterStateUpdated(function (Set $set) {
                    $set('price', function (Get $get): int {
                        return ((int) $get('unit_price') * (int) $get('quantity')) + (int) $get('shipping_price');
                    });
                }),

            TextInput::make('quantity')
                ->default(1)
                ->label('Cantidad')
                ->live()
                ->numeric()
                ->minValue(1)
                ->required()
                ->afterStateUpdated(function (Set $set) {
                    $set('price', function (Get $get): int {
                        return ((int) $get('unit_price') * (int) $get('quantity')) + (int) $get('shipping_price');
                    });
                }),

            TextInput::make('unit_price')
                ->label('Precio unitario')
                ->numeric()
                ->disabled()
                ->required()
                ->dehydrated()
                ->formatStateUsing(function (?OrderItem $record): int {
                    if (is_null($record)) {
                        return 0;
                    }

                    return $record->stockItem->price;
                }),

            TextInput::make('price')
                ->label('Total con envío')
                ->numeric()
                ->required()
                ->disabled()
                ->dehydrated()
                ->formatStateUsing(function (Get $get): int {
                    return ((int) $get('unit_price') * (int) $get('quantity')) + (int) $get('shipping_price');
                }),
        ];
    }
}
