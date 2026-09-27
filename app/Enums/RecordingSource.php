<?php

namespace App\Enums;

/**
 * Asal berkas rekaman. Perekaman di browser dan perekaman melalui perangkat
 * ESP32 menghasilkan berkas yang sama, bedanya hanya siapa yang mengirimnya.
 */
enum RecordingSource: string
{
    case Browser = 'browser';
    case Esp32 = 'esp32';

    /**
     * Get the label used in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Browser => 'Perekaman browser',
            self::Esp32 => 'Perangkat ESP32',
        };
    }

    /**
     * Get the badge styling used in the interface.
     */
    public function badge(): string
    {
        return match ($this) {
            self::Browser => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
            self::Esp32 => 'bg-violet-100 text-violet-800 dark:bg-violet-950 dark:text-violet-200',
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
            ->mapWithKeys(fn (self $source): array => [$source->value => $source->label()])
            ->all();
    }
}
