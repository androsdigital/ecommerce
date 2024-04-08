<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $title;
    public string $subTitle;
    public string $about;
    public string|null $logo;

    public static function group(): string
    {
        return 'general';
    }
}
