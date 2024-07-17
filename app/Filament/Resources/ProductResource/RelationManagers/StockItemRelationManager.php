<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Address;
use App\Models\Color;
use App\Models\Size;
use App\Models\StockItem;
use Filament\Forms\Components\Actions\Action;
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

                TextColumn::make('address.full_address')
                    ->label('Direccion')
                    ->toggleable(isToggledHiddenByDefault: true),

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

            Select::make('address_id')
                ->label('Dirección')
                ->live()
                ->required()
                ->searchable()
                ->relationship('address', 'full_address')
                ->columnSpanFull()
                ->suffixActions([
                    Action::make('editAddress')
                        ->label('Editar')
                        ->link()
                        ->icon('heroicon-m-pencil-square')
                        ->url(function (?int $state) {
                            if (is_null($state)) {
                                return route('filament.admin.resources.addresses.create');
                            }

                            return route('filament.admin.resources.addresses.edit', ['record' => Address::find($state)]);
                        }),

                    Action::make('createAddress')
                        ->label('Nueva')
                        ->color('success')
                        ->link()
                        ->icon('heroicon-m-pencil-square')
                        ->url(function () {
                            return route('filament.admin.resources.addresses.create');
                        }),
                ]),
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
