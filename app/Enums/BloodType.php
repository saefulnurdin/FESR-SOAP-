<?php

namespace App\Enums;

/**
 * Golongan darah pasien. Kasus TidakDiketahui dipakai sebagai tanda belum
 * diketahui.
 */
enum BloodType: string
{
    case A = 'A';
    case B = 'B';
    case AB = 'AB';
    case O = 'O';
    case TidakDiketahui = '-';

    /**
     * Get the label used in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::A => 'A',
            self::B => 'B',
            self::AB => 'AB',
            self::O => 'O',
            self::TidakDiketahui => 'Tidak diketahui',
        };
    }

    /**
     * Get the options for a select input.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
