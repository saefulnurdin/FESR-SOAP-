<?php

namespace App\Enums;

/**
 * Jenis kunjungan pasien. Nilai ini menentukan alur klinis pada fase
 * berikutnya, misalnya rekaman suara untuk rawat inap.
 */
enum VisitType: string
{
    case RawatJalan = 'rawat_jalan';
    case GawatDarurat = 'gawat_darurat';
    case RawatInap = 'rawat_inap';
    case Telekonsultasi = 'telekonsultasi';

    /**
     * Get the label used in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::RawatJalan => 'Rawat Jalan',
            self::GawatDarurat => 'Gawat Darurat',
            self::RawatInap => 'Rawat Inap',
            self::Telekonsultasi => 'Telekonsultasi',
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
