<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
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
                TextInput::make('name')
                    ->label('Nombre')
                    ->maxLength(255)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null
                    ),
                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->maxLength(255)
                    ->required()
                    ->unique(Product::class, 'slug', ignoreRecord: true),
                Textarea::make('description')
                    ->label('Descripción')
                    ->minLength(30)
                    ->maxLength(1000)
                    ->required()
                    ->columnSpanFull(),
                Repeater::make('inventoryItems')
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
                        TextInput::make('quantity')
                            ->label('Cantidad')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(9999)
                            ->numeric(),
                    ]),
                Repeater::make('features')
                    ->label('Características')
                    ->addActionLabel('Agregar característica')
                    ->defaultItems(0)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->minLength(2)
                            ->maxLength(50)
                            ->required()
                            ->alpha(),
                        TextInput::make('value')
                            ->label('Valor')
                            ->maxLength(500)
                            ->required(),
                    ]),
                Repeater::make('comments')
                    ->label('Comentarios')
                    ->addActionLabel('Agregar comentario')
                    ->defaultItems(0)
                    ->schema([
                        Textarea::make('comment')
                            ->label('Comentario')
                            ->minLength(30)
                            ->maxLength(500)
                            ->required(),
                    ]),
                SpatieMediaLibraryFileUpload::make('photo')
                    ->label('Foto')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(10000000)
                    ->numeric(),
                TextInput::make('price_before_discount')
                    ->label('Precio antes de descuento')
                    ->default('price')
                    ->gte('price')
                    ->integer()
                    ->minValue(0)
                    ->maxValue(10000000)
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money(),
                TextColumn::make('price_before_discount')
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
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit'   => EditProduct::route('/{record}/edit'),
        ];
    }
}
