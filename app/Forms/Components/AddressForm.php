<?php

namespace App\Forms\Components;

use App\Models\Address;
use App\Models\City;
use App\Models\State;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;

class AddressForm extends Field
{
    protected string $view = 'filament-forms::components.group';

    /** @var string|callable|null */
    public $relationship = null;

    public function relationship(string|callable $relationship): static
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function saveRelationships(): void
    {
        $state = $this->getState();
        $record = $this->getRecord();
        $relationship = $record?->{$this->getRelationship()}();

        if ($relationship === null) {
            return;
        } elseif ($address = $relationship->first()) {
            $address->update($state);
        } else {
            $relationship->updateOrCreate($state);
        }

        $record?->touch();
    }

    public function getChildComponents(): array
    {
        return [
            Select::make('street_type')
                ->label('Tipo de calle'),
            TextInput::make('street_number')
                ->label('Número de Calle'),
            TextInput::make('first_number')
                ->label('Número'),
            TextInput::make('second_number'),
            TextInput::make('apartment_number')
                ->label('Apartamento/Edificio'),
            Select::make('type')
                ->label('Tipo de residencia'),
            TextInput::make('phone')
                ->label('Teléfono de contacto'),
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

    public function getRelationship(): string
    {
        return $this->evaluate($this->relationship) ?? $this->getName();
    }
}
