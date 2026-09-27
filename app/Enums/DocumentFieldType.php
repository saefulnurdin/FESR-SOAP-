<?php

namespace App\Enums;

/**
 * Tipe isian pada satu field template dokumen. Tipe menentukan komponen
 * antarmuka yang dipakai saat dokumen diisi dan cara nilai dari hasil ekstraksi
 * NLU disimpan.
 */
enum DocumentFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Select = 'select';
    case Boolean = 'boolean';
    case Date = 'date';

    /**
     * Get the label used in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Text => 'Teks pendek',
            self::Textarea => 'Teks panjang',
            self::Number => 'Angka',
            self::Select => 'Pilihan',
            self::Boolean => 'Ya / Tidak',
            self::Date => 'Tanggal',
        };
    }

    /**
     * Whether the field requires a list of choices before it can be filled.
     */
    public function needsOptions(): bool
    {
        return $this === self::Select;
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
