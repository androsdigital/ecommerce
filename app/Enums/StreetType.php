<?php

namespace App\Enums;

use App\Traits\Arrayable;
use Filament\Support\Contracts\HasLabel;

enum StreetType: string implements HasLabel
{
    use Arrayable;

    case Avenida = 'avenida';
    case AvenidaCalle = 'avenida-calle';
    case AvenidaCarrera = 'avenida-carrera';
    case Calle = 'calle';
    case Carrera = 'carrera';
    case Circular = 'circular';
    case Circunvalar = 'circunvalar';
    case Diagonal = 'diagonal';
    case Manzana = 'manzana';
    case Transversal = 'transversal';
    case Via = 'via';

    public function getLabel(): string
    {
        return match ($this) {
            self::Avenida        => 'Avenida',
            self::AvenidaCalle   => 'Avenida Calle',
            self::AvenidaCarrera => 'Avenida Carrera',
            self::Calle          => 'Calle',
            self::Carrera        => 'Carrera',
            self::Circular       => 'Circular',
            self::Circunvalar    => 'Circunvalar',
            self::Diagonal       => 'Diagonal',
            self::Manzana        => 'Manzana',
            self::Transversal    => 'Transversal',
            self::Via            => 'Vía',
        };
    }
}
