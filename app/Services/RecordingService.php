<?php

namespace App\Services;

use App\Enums\RecordingSource;
use App\Models\Encounter;
use App\Models\EncounterRecording;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyimpan berkas rekaman ke disk privat dan mencatatnya pada kunjungan.
 *
 * Dipakai oleh perekaman di browser maupun unggahan dari perangkat ESP32,
 * sehingga keduanya menghasilkan data dengan bentuk yang sama.
 */
class RecordingService
{
    /**
     * Store an uploaded audio file and register it on the encounter.
     *
     * @param  int|null  $durationSeconds  Panjang rekaman, diukur oleh pengirim
     *                                     karena server tidak dapat memverifikasinya
     *                                     tanpa ffmpeg.
     * @param  \DateTimeInterface|string|null  $capturedAt  Waktu rekaman dibuat,
     *                                                      yang bisa berbeda dari waktu unggah.
     * @param  string|null  $transcript  Catatan yang diisi pengirim, boleh dikosongkan
     *                                   dan dilengkapi nanti.
     */
    public function store(
        Encounter $encounter,
        UploadedFile $audio,
        User $recordedBy,
        RecordingSource $source = RecordingSource::Browser,
        ?int $durationSeconds = null,
        \DateTimeInterface|string|null $capturedAt = null,
        ?string $transcript = null,
    ): EncounterRecording {
        $disk = (string) config('fesr.audio.disk');
        $path = $this->pathFor($encounter, $audio, $source);

        $audio->storeAs(dirname($path), basename($path), ['disk' => $disk]);

        return $encounter->recordings()->create([
            'recorded_by' => $recordedBy->getKey(),
            'source' => $source,
            'mime_type' => $audio->getMimeType() ?? 'application/octet-stream',
            'size_bytes' => Storage::disk($disk)->size($path),
            'duration_seconds' => $durationSeconds,
            'storage_disk' => $disk,
            'storage_path' => $path,
            'original_name' => $audio->getClientOriginalName(),
            'captured_at' => $capturedAt,
            'transcript' => $transcript,
        ]);
    }

    /**
     * Delete the audio file of a recording from its disk.
     *
     * Berkas diarsipkan bersama datanya; method ini dipakai ketika arsip
     * dipulihkan supaya berkas basi tidak tertinggal.
     */
    public function forgetFile(EncounterRecording $recording): void
    {
        Storage::disk($recording->storage_disk)->delete($recording->storage_path);
    }

    /**
     * Susun lokasi berkas yang tidak menebak dan tidak bisa ditimpa.
     *
     * @return array{0: string, 1: string} Disk dan lokasi berkas.
     */
    private function pathFor(Encounter $encounter, UploadedFile $audio, RecordingSource $source): string
    {
        $directory = sprintf(
            '%s/%d/%s',
            trim((string) config('fesr.audio.path'), '/'),
            $encounter->getKey(),
            $source->value,
        );

        /*
         * Ekstensi diturunkan dari tipe MIME berkas, bukan dari nama yang
         * dikirim klien, supaya nama berkas tidak dapat menyamar menjadi
         * berkas lain.
         */
        $extension = $audio->guessExtension() ?: 'bin';

        return sprintf('%s/%s.%s', $directory, Str::uuid()->toString(), $extension);
    }
}
