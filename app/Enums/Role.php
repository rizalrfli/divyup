<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Tim = 'tim';
    case Klien = 'klien';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Pimpinan',
            self::Tim => 'Tim Operasional',
            self::Klien => 'Klien',
        };
    }
}
