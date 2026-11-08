<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Menunggu = 'menunggu';
    case Disetujui = 'disetujui';
    case Revisi = 'revisi';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu persetujuan',
            self::Disetujui => 'Disetujui',
            self::Revisi => 'Diminta revisi',
        };
    }
}
