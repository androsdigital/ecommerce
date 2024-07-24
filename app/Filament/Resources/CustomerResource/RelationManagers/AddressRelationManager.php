<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Filament\Resources\AddressResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressRelationManager extends RelationManager
{
    protected static ?string $title = 'Direcciónes del Comprador';

    protected static ?string $modelLabel = 'Dirección';

    protected static string $relationship = 'addresses';

    public function form(Form $form): Form
    {
        return AddressResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_address')
                    ->label('Direccion')
                    ->sortable(),
                TextColumn::make('building')
                    ->label('Unidad/Conjunto/Edificio')
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Teléfono de contacto')
                    ->sortable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Vincular Direccion'),
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->bulkActions([
                DetachBulkAction::make(),
            ])
            ->recordTitleAttribute('full_address');
    }
}
