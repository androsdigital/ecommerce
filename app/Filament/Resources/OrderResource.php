<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use App\Models\Order;
use App\Traits\HasAddress;
use Exception;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
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
    use HasAddress;

    protected static ?string $model = Order::class;

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema(static::getDetailsFormSchema())
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),

                Section::make()
                    ->schema(self::getPlaceholdersFormSchema())
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Order $record) => $record === null),
            ])
            ->columns(3);
    }

    /**
     * @throws Exception
     */
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
            OrderItemRelationManager::class,
            //            PaymentsRelationManager::class,
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
            'index' => ListOrders::route('/'),
            'edit'  => EditOrder::route('/{record}/edit'),
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
                ->default(fake()->unique()->numerify('OR-######'))
                ->disabled()
                ->dehydrated()
                ->required()
                ->maxLength(31)
                ->unique(Order::class, 'number', ignoreRecord: true),

            Select::make('customer_id')
                ->label('Comprador')
                ->relationship('customer', 'name')
                ->disabled(),

            ToggleButtons::make('status')
                ->label('Estado')
                ->inline()
                ->options(OrderStatus::class)
                ->columnSpanFull()
                ->required(),

            Section::make()
                ->heading('Dirección')
                ->collapsed()
                ->relationship('address')
                ->schema(static::getAddressFormSchema()),

            MarkdownEditor::make('notes')
                ->label('Observaciones')
                ->columnSpan('full'),
        ];
    }

    public static function getPlaceholdersFormSchema(): array
    {
        return [
            Placeholder::make('total_price')
                ->label('Total')
                ->content(fn (Order $record): string => number_format($record->total_price, thousands_separator: '.')),

            Placeholder::make('total_price_before_discount')
                ->label('SubTotal')
                ->content(fn (Order $record): string => number_format($record->total_price_before_discount, thousands_separator: '.')),

            Placeholder::make('total_items_discount')
                ->label('Descuento de los Productos')
                ->content(fn (Order $record): string => number_format($record->total_items_discount, thousands_separator: '.')),

            Placeholder::make('discount')
                ->label('Descuento del Pedido')
                ->content(fn (Order $record): string => number_format($record->discount, thousands_separator: '.')),

            Placeholder::make('total_discount')
                ->label('Descuento Total')
                ->content(fn (Order $record): string => number_format($record->total_discount, thousands_separator: '.')),

            Placeholder::make('total_quantity')
                ->label('Cantidad de Productos')
                ->content(fn (Order $record): string => number_format($record->total_quantity, thousands_separator: '.')),

            Placeholder::make('created_at')
                ->label('Creado')
                ->content(fn (Order $record): string => $record->created_at?->diffForHumans()),

            Placeholder::make('updated_at')
                ->label('Modificado')
                ->content(fn (Order $record): string => $record->updated_at?->diffForHumans()),
        ];
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

    public static function canCreate(): bool
    {
        return false;
    }
}
