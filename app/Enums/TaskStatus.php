<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Belum = 'belum';
    case Proses = 'proses';
    case Selesai = 'selesai';
    case Dilewati = 'dilewati';

    public function label(): string
    {
        return match ($this) {
            self::Belum => 'Belum mulai',
            self::Proses => 'Sedang dikerjakan',
            self::Selesai => 'Selesai',
            self::Dilewati => 'Dilewati',
        };
    }
}
