<?php

namespace App\Enums;

enum Stage: string
{
    case Briefing = 'briefing';
    case Desain = 'desain';
    case Review = 'review';
    case Revisi = 'revisi';
    case SiapPublikasi = 'siap_publikasi';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::Briefing->value => 'Briefing',
            self::Desain->value => 'Pengerjaan Desain',
            self::Review->value => 'Menunggu Review',
            self::Revisi->value => 'Revisi',
            self::SiapPublikasi->value => 'Siap Publikasi',
        ];
    }
}
