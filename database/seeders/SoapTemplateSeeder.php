<?php

namespace Database\Seeders;

use App\Enums\DocumentFieldType;
use App\Models\DocumentType;
use Illuminate\Database\Seeder;

/**
 * Menyiapkan template SOAP yang menjadi target pemetaan hasil rekaman suara.
 * Seeder ini idempoten: menjalankannya berkali-kali tidak menggandakan data.
 */
class SoapTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $documentType = DocumentType::query()->firstOrCreate(
            ['code' => 'soap'],
            [
                'name' => 'Catatan SOAP',
                'description' => 'Catatan kunjungan yang memisahkan keluhan, hasil pemeriksaan, penilaian, dan rencana tata laksana.',
            ],
        );

        $template = $documentType->templates()->firstOrCreate(
            ['name' => 'SOAP Dewasa'],
            [
                'description' => 'Struktur SOAP untuk pasien dewasa. Satu isian mewakili satu nilai yang dapat diambil dari rekaman suara.',
            ],
        );

        if ($template->sections()->exists()) {
            return;
        }

        $sections = [
            [
                'key' => 'subjective',
                'title' => 'Subjective',
                'hint' => 'Keluhan dan riwayat yang diceritakan pasien atau keluarga, bukan hasil pemeriksaan.',
                'fields' => [
                    ['key' => 'keluhan_utama', 'label' => 'Keluhan utama', 'type' => DocumentFieldType::Textarea, 'is_required' => true],
                    ['key' => 'riwayat_penyakit', 'label' => 'Riwayat penyakit', 'type' => DocumentFieldType::Textarea],
                    ['key' => 'riwayat_alergi', 'label' => 'Riwayat alergi', 'type' => DocumentFieldType::Textarea],
                    ['key' => 'riwayat_obat', 'label' => 'Riwayat konsumsi obat', 'type' => DocumentFieldType::Textarea],
                ],
            ],
            [
                'key' => 'objective',
                'title' => 'Objective',
                'hint' => 'Hasil pemeriksaan yang dilakukan petugas beserta angka yang terukur.',
                'fields' => [
                    ['key' => 'tekanan_darah', 'label' => 'Tekanan darah', 'type' => DocumentFieldType::Text, 'unit' => 'mmHg'],
                    ['key' => 'denyut_nadi', 'label' => 'Denyut nadi', 'type' => DocumentFieldType::Number, 'unit' => '/menit'],
                    ['key' => 'suhu', 'label' => 'Suhu tubuh', 'type' => DocumentFieldType::Number, 'unit' => '°C'],
                    ['key' => 'berat_badan', 'label' => 'Berat badan', 'type' => DocumentFieldType::Number, 'unit' => 'kg'],
                    ['key' => 'pemeriksaan_fisik', 'label' => 'Pemeriksaan fisik', 'type' => DocumentFieldType::Textarea],
                ],
            ],
            [
                'key' => 'assessment',
                'title' => 'Assessment',
                'hint' => 'Penilaian klinis petugas berupa diagnosis dan pertimbangan banding.',
                'fields' => [
                    ['key' => 'diagnosis', 'label' => 'Diagnosis', 'type' => DocumentFieldType::Textarea, 'is_required' => true],
                    ['key' => 'diagnosis_banding', 'label' => 'Diagnosis banding', 'type' => DocumentFieldType::Textarea],
                ],
            ],
            [
                'key' => 'plan',
                'title' => 'Plan',
                'hint' => 'Rencana tata laksana, terapi, dan instruksi yang diberikan kepada pasien.',
                'fields' => [
                    ['key' => 'terapi', 'label' => 'Terapi', 'type' => DocumentFieldType::Textarea, 'is_required' => true],
                    ['key' => 'instruksi', 'label' => 'Instruksi kepada pasien', 'type' => DocumentFieldType::Textarea],
                    ['key' => 'kontrol', 'label' => 'Rencana kontrol', 'type' => DocumentFieldType::Text],
                ],
            ],
        ];

        foreach ($sections as $order => $section) {
            $created = $template->sections()->create([
                'key' => $section['key'],
                'title' => $section['title'],
                'hint' => $section['hint'],
                'sort_order' => $order + 1,
            ]);

            foreach ($section['fields'] as $position => $field) {
                $created->fields()->create([
                    ...$field,
                    'options' => null,
                    'unit' => $field['unit'] ?? null,
                    'sort_order' => $position + 1,
                ]);
            }
        }
    }
}
