<?php

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.title', 'Filament E-shop');
        $this->migrator->add('general.subTitle', 'Awesome shop');
        $this->migrator->add('general.about', 'Awesome shop made with Filament by Laravel Daily');

        // Logo
        Storage::deleteDirectory('public', true);
        Storage::copy('logo.png', 'public/logo.png');
        $this->migrator->add('general.logo', 'logo.png');
    }
};
