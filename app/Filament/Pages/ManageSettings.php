<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Configuración';

    protected static string $settings = GeneralSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título'),
                Forms\Components\TextInput::make('subTitle')
                    ->label('Subtítulo'),
                Forms\Components\Textarea::make('about')
                    ->label('Acerca de')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('logo')
                    ->columnSpanFull(),
            ]);
    }
}
