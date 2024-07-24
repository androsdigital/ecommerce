<?php

namespace App\Filament\Resources;

use App\Enums\StreetType;
use App\Filament\Resources\AddressResource\Pages\CreateAddress;
use App\Filament\Resources\AddressResource\Pages\EditAddress;
use App\Filament\Resources\AddressResource\Pages\ListAddresses;
use App\Models\Address;
use App\Models\City;
use App\Models\State;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Dirección';

    protected static ?string $pluralModelLabel = 'Direcciones';

    public static function form(Form $form): Form
    {
        $fullAddressCallback = function (Get $get, Set $set): void {
            $apartment = $get('apartment') === '' ? '' : ' Apto ' . $get('apartment');
            $streetType = is_null($get('street_type')) || $get('street_type') === ''
                ? ''
                : StreetType::from($get('street_type'))->getLabel();

            $set('full_address',
                City::find($get('city_id'))?->name
                . ' - ' . State::find($get('state_id'))?->name
                . ', ' . $streetType
                . ' ' . $get('street_number')
                . ' # ' . $get('first_number')
                . ' - ' . $get('second_number')
                . $apartment);
        };

        return $form
            ->schema([
                TextInput::make('full_address')
                    ->label('Dirección Completa')
                    ->maxLength(255)
                    ->required()
                    ->disabled()
                    ->dehydrated(),

                Select::make('state_id')
                    ->label('Departamento')
                    ->native(false)
                    ->live()
                    ->required()
                    ->dehydrated(false)
                    ->options(State::pluck('name', 'id'))
                    ->afterStateUpdated(function (Set $set) {
                        $set('city_id', '');
                    }),

                Select::make('city_id')
                    ->searchable()
                    ->label('Ciudad')
                    ->native(false)
                    ->live()
                    ->required()
                    ->options(function (?Address $record, Get $get, Set $set) {
                        if (! is_null($record) && $get('state_id') === null) {
                            $state = $record->city->state->id;

                            $set('state_id', $state);
                        }

                        return City::where('state_id', $get('state_id'))->pluck('name', 'id');
                    })
                    ->afterStateUpdated($fullAddressCallback)
                    ->live(),

                Select::make('street_type')
                    ->label('Tipo de calle')
                    ->live()
                    ->options(StreetType::class)
                    ->required()
                    ->afterStateUpdated($fullAddressCallback),

                TextInput::make('street_number')
                    ->label('Número de Calle')
                    ->maxLength(31)
                    ->required()
                    ->afterStateUpdated($fullAddressCallback)
                    ->live(),

                TextInput::make('first_number')
                    ->label('Número')
                    ->maxLength(31)
                    ->required()
                    ->afterStateUpdated($fullAddressCallback)
                    ->live(),

                TextInput::make('second_number')
                    ->maxLength(31)
                    ->required()
                    ->afterStateUpdated($fullAddressCallback)
                    ->live(),

                TextInput::make('apartment')
                    ->maxLength(31)
                    ->label('Apartamento')
                    ->afterStateUpdated($fullAddressCallback)
                    ->live(),

                TextInput::make('building')
                    ->maxLength(255)
                    ->label('Unidad/Conjunto/Edificio'),

                TextInput::make('phone')
                    ->label('Teléfono de contacto')
                    ->maxLength(31)
                    ->required(),

                TextInput::make('observation')
                    ->label('Observación'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('city.state.name')
                    ->label('Departamento')
                    ->sortable(),
                TextColumn::make('city.name')
                    ->label('Ciudad')
                    ->sortable(),
                TextColumn::make('full_address')
                    ->label('Direccion')
                    ->searchable(),
                TextColumn::make('building')
                    ->label('Unidad/Conjunto/Edificio')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono de contacto')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('location')
                    ->label('Locación'),
                TextColumn::make('created_at')
                    ->label('Creada el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Modificada el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(self::getTableFilters())
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
            'index'  => ListAddresses::route('/'),
            'create' => CreateAddress::route('/create'),
            'edit'   => EditAddress::route('/{record}/edit'),
        ];
    }

    public static function getTableFilters(): array
    {
        return [
            Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from')
                        ->label('Direcciones creadas desde')
                        ->native(false)
                        ->placeholder(fn ($state): string => now()->format('d/m/Y'))
                        ->displayFormat('d/m/Y'),
                    DatePicker::make('created_until')
                        ->label('Direcciones creadas hasta')
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
                        $indicators['created_from'] = 'Direcciones desde ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                    }
                    if ($data['created_until'] ?? null) {
                        $indicators['created_until'] = 'Direcciones hasta ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                    }

                    return $indicators;
                }),
        ];
    }
}
