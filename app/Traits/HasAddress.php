<?php

namespace App\Traits;

use App\Enums\StreetType;
use App\Models\Address;
use App\Models\City;
use App\Models\State;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;

trait HasAddress
{
    protected static function getAddressFormSchema(): array
    {
        return [
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
        ];
    }
}
