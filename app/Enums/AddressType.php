<?php

namespace App\Enums;

use App\Traits\Arrayable;
use Filament\Support\Contracts\HasLabel;

enum AddressType: string implements HasLabel
{
    use Arrayable;

    case Urban = 'urban';
    case Rural = 'rural';

    public function getLabel(): string
    {
        return match ($this) {
            self::Urban => 'Urbano',
            self::Rural => 'Rural',
        };
    }
}
