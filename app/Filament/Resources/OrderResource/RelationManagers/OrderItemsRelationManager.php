<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\State;
use App\Models\StockItem;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static ?string $title = 'Items del Pedido';
    protected static ?string $modelLabel = 'item';
    protected static string $relationship = 'orderItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->relationship('stockItem')
                    ->schema([
                        Select::make('product_id')
                            ->label('Producto')
                            ->live()
                            ->searchable()
                            ->native(false)
                            ->dehydrated(false)
                            ->options(Product::pluck('name', 'id'))
                            ->afterStateUpdated(function (Set $set) {
                                $set('size_id', '');
                                $set('color_id', '');
                            }),
                        Select::make('size_id')
                            ->label('Talla')
                            ->live()
                            ->searchable()
                            ->native(false)
                            ->dehydrated(false)
                            ->options(function (Get $get) {
                                return Product::find($get('product_id'))->sizes->pluck('name', 'id');
                            })
                            ->afterStateUpdated(function (Set $set) {
                                $set('color_id', '');
                            }),
                        Select::make('color_id')
                            ->searchable()
                            ->label('City')
                            ->native(false)
                            ->options(function (?StockItem $record, Get $get, Set $set) {
                                if (! is_null($record) && $get('product_id') === null && $get('size_id') === null) {
                                    $product = $record->product->id;
                                    $size = $record->size->id;

                                    $set('product_id', $product);
                                    $set('size_id', $size);
                                }

                                return Product::find($get('product_id'))->colors->where('size_id', $get('size_id'))
                                    ->where('product_id', $get('product_id'))
                                    ->pluck('name', 'id');
                            }),
                    ]),

                TextInput::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->default(1)
                    ->required(),

                TextInput::make('shipping_price')
                    ->label('Costo de envío')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stockItem.product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stockItem.size.name')
                    ->label('Talla')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stockItem.color.name')
                    ->label('Color')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(locale: 'es')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('unit_price')
                    ->label('Precio Unitario')
                    ->getStateUsing(function (OrderItem $record): string {
                        return $record->stockItem->price_before_discount
                            - $record->stockItem->discount
                            + $record->shipping_price;
                    })
                    ->numeric(locale: 'es')
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Precio')
                    ->getStateUsing(function (OrderItem $record): string {
                        return ($record->stockItem->price_before_discount
                            - $record->stockItem->discount
                            + $record->shipping_price)
                            * $record->quantity;
                    })
                    ->numeric(locale: 'es')
                    ->toggleable()
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

                TextColumn::make('shipping_price')
                    ->label('Costo de envío')
                    ->numeric(locale: 'es')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
