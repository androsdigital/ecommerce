<?php

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.title', 'Ecommerce');
        $this->migrator->add('general.subTitle', 'La tienda de las mejores promociones');
        $this->migrator->add('general.about', 'Aquí encontrarás los mejores descuentos y ofertas de tu elección. ¡No te las pierdas!');

        // Logo
        Storage::deleteDirectory('public', true);
        Storage::copy('logo.png', 'public/logo.png');
        $this->migrator->add('general.logo', 'logo.png');
    }
};
