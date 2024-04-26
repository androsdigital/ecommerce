<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $modelLabel = 'Producto';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                Forms\Components\Repeater::make('inventoryItems')
                    ->label('Inventario')
                    ->addActionLabel('Agregar elemento')
                    ->relationship()
                    ->deleteAction(
                        fn (Action $action) => $action->requiresConfirmation()->label('Esta acción eliminará el elemento del inventario.'),
                    )
                    ->defaultItems(0)
                    ->schema([
                        Select::make('size_id')
                            ->label('Talla')
                            ->relationship('size', 'name')
                            ->required(),
                        Select::make('color_id')
                            ->label('Color')
                            ->relationship('color', 'name')
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->required()
                            ->numeric(),
                    ]),
                Forms\Components\Repeater::make('features')
                    ->label('Características')
                    ->addActionLabel('Agregar característica')
                    ->defaultItems(0)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        Forms\Components\TextInput::make('value')
                            ->label('Valor')
                            ->required(),
                    ]),
                Forms\Components\Repeater::make('comments')
                    ->label('Comentarios')
                    ->addActionLabel('Agregar comentario')
                    ->defaultItems(0)
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->label('Comentario')
                            ->required(),
                    ]),
                Forms\Components\TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique(Product::class, 'slug', ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\SpatieMediaLibraryFileUpload::make('photo')
                    ->label('Foto')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->step(100),
                Forms\Components\TextInput::make('price_before_discount')
                    ->label('Precio antes de descuento')
                    ->numeric()
                    ->step(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money(),
                Tables\Columns\TextColumn::make('price_before_discount')
                    ->label('Precio antes de descuento')
                    ->money(),
                TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->getStateUsing(fn (Product $product) => $product->inventoryItems->sum('quantity')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
