<?php

namespace App\Enums;

/**
 * Status kunjungan. Kunjungan yang berstatus Berjalan masih dapat diisi
 * rekaman suara dan draf SOAP; setelah Selesai, kunjungan menjadi read-only.
 */
enum EncounterStatus: string
{
    case Berjalan = 'berjalan';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    /**
     * Get the label used in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Berjalan => 'Berjalan',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /**
     * Get the badge styling used in the interface.
     */
    public function badge(): string
    {
        return match ($this) {
            self::Berjalan => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
            self::Selesai => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
            self::Dibatalkan => 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
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
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
