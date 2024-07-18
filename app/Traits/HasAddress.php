<?php

namespace App\Traits;

use App\Models\Address;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;

trait HasAddress
{
    protected static function getAddressFormComponent(): Select
    {
        return Select::make('address_id')
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
            ]);
    }
}
