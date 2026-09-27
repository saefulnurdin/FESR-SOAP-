<?php

namespace App\Models;

use App\Enums\RecordingSource;
use Database\Factories\EncounterRecordingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Satu rekaman kondisi pasien pada sebuah kunjungan. Berkas audio disimpan di
 * disk privat dan tidak punya URL publik; pemutarannya dialirkan lewat
 * controller yang memeriksa hak akses.
 */
#[Fillable([
    'encounter_id',
    'recorded_by',
    'source',
    'mime_type',
    'size_bytes',
    'duration_seconds',
    'storage_disk',
    'storage_path',
    'original_name',
    'captured_at',
    'transcript',
])]
class EncounterRecording extends Model
{
    /** @use HasFactory<EncounterRecordingFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => RecordingSource::class,
            'size_bytes' => 'integer',
            'duration_seconds' => 'integer',
            'captured_at' => 'datetime',
        ];
    }

    /**
     * Kunjungan tempat rekaman ini dibuat.
     *
     * @return BelongsTo<Encounter, $this>
     */
    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    /**
     * Petugas yang mengunggah rekaman, atau pemilik perangkat yang
     * mengirimkannya.
     *
     * @return BelongsTo<User, $this>
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Whether the audio file is still present on its disk.
     */
    public function fileExists(): bool
    {
        return Storage::disk($this->storage_disk)->exists($this->storage_path);
    }

    /**
     * Ukuran berkas dalam format yang enak dibaca manusia.
     */
    public function humanSize(): string
    {
        $kilobytes = $this->size_bytes / 1024;

        return $kilobytes >= 1024
            ? number_format($kilobytes / 1024, 1, ',', '.').' MB'
            : number_format($kilobytes, 0, ',', '.').' KB';
    }

    /**
     * Durasi dalam format jam:menit:detik.
     */
    public function humanDuration(): string
    {
        if ($this->duration_seconds === null) {
            return 'Tidak diketahui';
        }

        return sprintf(
            '%02d:%02d',
            intdiv($this->duration_seconds, 60),
            $this->duration_seconds % 60,
        );
    }
}
