<?php

namespace App\Filament\Resources;

use App\Enums\StreetType;
use App\Filament\Resources\AddressResource\Pages\CreateAddress;
use App\Filament\Resources\AddressResource\Pages\EditAddress;
use App\Filament\Resources\AddressResource\Pages\ListAddresses;
use App\Models\Address;
use App\Models\City;
use App\Models\State;
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
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Dirección';

    protected static ?string $pluralModelLabel = 'Direcciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('street_type')
                    ->label('Tipo de calle')
                    ->options(StreetType::class)
                    ->required(),

                TextInput::make('street_number')
                    ->label('Número de Calle')
                    ->maxLength(31)
                    ->required(),

                TextInput::make('first_number')
                    ->label('Número')
                    ->maxLength(31)
                    ->required(),

                TextInput::make('second_number')
                    ->maxLength(31)
                    ->required(),

                TextInput::make('apartment')
                    ->maxLength(255)
                    ->label('Apartamento/Edificio'),

                TextInput::make('phone')
                    ->label('Teléfono de contacto')
                    ->maxLength(31)
                    ->required(),

                Select::make('state_id')
                    ->label('Departamento')
                    ->native(false)
                    ->dehydrated(false)
                    ->options(State::pluck('name', 'id'))
                    ->afterStateUpdated(function (Set $set) {
                        $set('city_id', '');
                    }),

                Select::make('city_id')
                    ->searchable()
                    ->label('Ciudad')
                    ->native(false)
                    ->required()
                    ->options(function (?Address $record, Get $get, Set $set) {
                        if (! is_null($record) && $get('state_id') === null) {
                            $state = $record->city->state->id;

                            $set('state_id', $state);
                        }

                        return City::where('state_id', $get('state_id'))->pluck('name', 'id');
                    }),

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
                TextColumn::make('phone')
                    ->label('Teléfono de contacto')
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
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAddresses::route('/'),
            'create' => CreateAddress::route('/create'),
            'edit'   => EditAddress::route('/{record}/edit'),
        ];
    }
}
