<?php

namespace App\Enums;

/**
 * Jenis kelamin pasien, mengikuti kode yang lazim dipakai pada rekam medis
 * Indonesia.
 */
enum Gender: string
{
    case LakiLaki = 'L';
    case Perempuan = 'P';

    /**
     * Get the label used in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::LakiLaki => 'Laki-laki',
            self::Perempuan => 'Perempuan',
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
            ->mapWithKeys(fn (self $gender): array => [$gender->value => $gender->label()])
            ->all();
    }
}
