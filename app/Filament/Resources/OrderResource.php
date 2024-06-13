<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use App\Models\Order;
use App\Models\StockItem;
use Exception;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group as TableGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getTableColumns())
            ->filters(self::getTableFilters())
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->groups([
                TableGroup::make('created_at')
                    ->label('Fecha de creación')
                    ->date()
                    ->collapsible(),
                TableGroup::make('customer.name')
                    ->label('Comprador')
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //PaymentsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            //OrderStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit'   => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'customer.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Order $record */

        return [
            'Customer' => optional($record->customer)->name,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['customer', 'orderItems']);
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::where('status', 'new')->count();
    }

    public static function getDetailsFormSchema(): array
    {
        return [
            TextInput::make('number')
                ->label('Número')
                ->default('OR-' . random_int(100000, 999999))
                ->disabled()
                ->dehydrated()
                ->required()
                ->maxLength(32)
                ->unique(Order::class, 'number', ignoreRecord: true),

            Select::make('customer_id')
                ->label('Comprador')
                ->relationship('customer', 'name')
                ->searchable()
                ->required(),

            ToggleButtons::make('status')
                ->inline()
                ->options(OrderStatus::class)
                ->required(),

            MarkdownEditor::make('notes')
                ->label('Observaciones')
                ->columnSpan('full'),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('orderItems')
            ->relationship()
            ->schema([
                Select::make('stock_item_id')
                    ->relationship('stockItem', 'product.name')
                    ->label('Producto')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, Set $set) => $set('unit_price', StockItem::find($state)?->unit_price ?? 0))
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan([
                        'md' => 5,
                    ])
                    ->searchable(),

                TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->columnSpan([
                        'md' => 2,
                    ])
                    ->required(),

                TextInput::make('unit_price')
                    ->label('Unit Price')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan([
                        'md' => 3,
                    ]),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ])
            ->required();
    }

    public static function getTableColumns(): array
    {
        return [
            TextColumn::make('customer.name')
                ->label('Comprador')
                ->toggleable()
                ->searchable()
                ->sortable(),

            TextColumn::make('number')
                ->label('Número')
                ->toggleable()
                ->searchable()
                ->sortable(),

            TextColumn::make('status')
                ->label('Estado')
                ->toggleable()
                ->badge(),

            TextColumn::make('total_price')
                ->label('Precio total')
                ->toggleable()
                ->numeric(locale: 'es')
                ->sortable()
                ->summarize(Sum::make('sum')->label('Total')->numeric(locale: 'es')),

            TextColumn::make('address.full_address')
                ->label('Dirección de entrega')
                ->toggleable()
                ->searchable(),

            TextColumn::make('total_price_before_discount')
                ->label('Precio total sin descuento')
                ->toggleable(isToggledHiddenByDefault: true)
                ->numeric(locale: 'es')
                ->sortable()
                ->summarize(Sum::make('sum')->label('Total')->numeric(locale: 'es')),

            TextColumn::make('total_items_discount')
                ->label('Descuento total en los productos')
                ->toggleable(isToggledHiddenByDefault: true)
                ->numeric(locale: 'es')
                ->sortable()
                ->summarize(Sum::make('sum')->label('Total')->numeric(locale: 'es')),

            TextColumn::make('discount')
                ->label('Descuento propio')
                ->toggleable(isToggledHiddenByDefault: true)
                ->numeric(locale: 'es')
                ->sortable()
                ->summarize(Sum::make('sum')->label('Total')->numeric(locale: 'es')),

            TextColumn::make('total_discount')
                ->label('Descuento total')
                ->toggleable(isToggledHiddenByDefault: true)
                ->numeric(locale: 'es')
                ->sortable()
                ->summarize(Sum::make('sum')->label('Total')->numeric(locale: 'es')),

            TextColumn::make('total_shipping_price')
                ->label('Costo total de envío')
                ->toggleable(isToggledHiddenByDefault: true)
                ->numeric(locale: 'es')
                ->sortable()
                ->summarize(Sum::make('sum')->label('Total')->numeric(locale: 'es')),

            TextColumn::make('total_quantity')
                ->label('Cantidad de productos')
                ->toggleable(isToggledHiddenByDefault: true)
                ->numeric(locale: 'es')
                ->sortable()
                ->summarize(Sum::make('sum')->label('Total')->numeric(locale: 'es')),

            TextColumn::make('created_at')
                ->label('Creado el')
                ->toggleable(isToggledHiddenByDefault: true)
                ->date()
                ->sortable(),

            TextColumn::make('updated_at')
                ->label('Actualizado el')
                ->toggleable(isToggledHiddenByDefault: true)
                ->date()
                ->sortable(),
        ];
    }

    public static function getTableFilters(): array
    {
        return [
            TrashedFilter::make(),
            Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from')
                        ->label('Pedidos creados desde')
                        ->native(false)
                        ->placeholder(fn ($state): string => now()->format('d/m/Y'))
                        ->displayFormat('d/m/Y'),
                    DatePicker::make('created_until')
                        ->label('Pedidos creados hasta')
                        ->native(false)
                        ->placeholder(fn ($state): string => now()->format('d/m/Y'))
                        ->displayFormat('d/m/Y'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['created_from'] ?? null) {
                        $indicators['created_from'] = 'Pedidos desde ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                    }
                    if ($data['created_until'] ?? null) {
                        $indicators['created_until'] = 'Pedidos hasta ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                    }

                    return $indicators;
                }),
        ];
    }
}
