<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Color;
use App\Models\Size;
use App\Models\StockItem;
use App\Traits\HasAddress;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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

class StockItemRelationManager extends RelationManager
{
    use HasAddress;

    protected static ?string $title = 'Stock Items';

    protected static ?string $modelLabel = 'item';

    protected static string $relationship = 'stockItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(3)
                    ->schema($this->getDetailsFormSchema()),

                Section::make()
                    ->columns(2)
                    ->schema($this->getPricingFormSchema()),

                SpatieMediaLibraryFileUpload::make('photos')
                    ->label('Fotos')
                    ->multiple()
                    ->image()
                    ->reorderable()
                    ->maxFiles(10)
                    ->maxSize(4000)
                    ->columnSpanFull(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('size.name')
                    ->label('Talla')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('color.name')
                    ->label('Color')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(locale: 'es')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('price')
                    ->label('Precio')
                    ->numeric(locale: 'es')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('price_before_discount')
                    ->label('Precio antes del descuento')
                    ->numeric(locale: 'es')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('discount')
                    ->label('Descuento')
                    ->numeric(locale: 'es')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    protected function getDetailsFormSchema(): array
    {
        return [
            Select::make('size_id')
                ->label('Talla')
                ->required()
                ->searchable()
                ->native(false)
                ->options(Size::pluck('name', 'id')),

            Select::make('color_id')
                ->searchable()
                ->required()
                ->label('Color')
                ->native(false)
                ->options(Color::pluck('name', 'id')),

            TextInput::make('sku')
                ->dehydrated()
                ->label('SKU')
                ->default(fake()->numerify('SKU-##########'))
                ->required()
                ->unique(StockItem::class, 'sku', ignoreRecord: true)
                ->maxLength(14),

            Section::make()
                ->heading('Dirección')
                ->collapsed()
                ->relationship('address')
                ->schema(static::getAddressFormSchema()),
        ];
    }

    protected function getPricingFormSchema(): array
    {
        return [
            TextInput::make('quantity')
                ->default(1)
                ->label('Cantidad')
                ->numeric()
                ->minValue(1)
                ->required(),

            TextInput::make('price')
                ->label('Precio')
                ->default(0)
                ->numeric()
                ->disabled()
                ->dehydrated()
                ->required()
                ->minValue(0),

            TextInput::make('price_before_discount')
                ->label('Precio antes del descuento')
                ->live()
                ->default(0)
                ->numeric()
                ->required()
                ->minValue(0)
                ->afterStateUpdated(function (Get $get, Set $set): void {
                    $set('price', (int) $get('price_before_discount') - (int) $get('discount'));
                }),

            TextInput::make('discount')
                ->label('Descuento')
                ->live()
                ->default(0)
                ->numeric()
                ->required()
                ->maxValue(function (Get $get): int {
                    return (int) $get('price_before_discount');
                })
                ->minValue(0)
                ->afterStateUpdated(function (Get $get, Set $set): void {
                    $set('price', (int) $get('price_before_discount') - (int) $get('discount'));
                }),
        ];
    }
}
