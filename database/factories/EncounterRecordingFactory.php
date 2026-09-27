<?php

namespace Database\Factories;

use App\Enums\RecordingSource;
use App\Models\Encounter;
use App\Models\EncounterRecording;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<EncounterRecording>
 */
class EncounterRecordingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<EncounterRecording>
     */
    protected $model = EncounterRecording::class;

    public function definition(): array
    {
        $extension = fake()->randomElement(['webm', 'ogg', 'mp3', 'wav']);
        $recordedBy = User::factory();

        return [
            /*
             * Kunjungan dibuat oleh petugas yang sama dengan penguploadnya,
             * supaya rekaman hasil factory selalu punya petugas yang
             * menjalankannya.
             */
            'encounter_id' => Encounter::factory()->handledBy($recordedBy),
            'recorded_by' => $recordedBy,
            'source' => RecordingSource::Browser,
            'mime_type' => 'audio/'.$extension,
            'size_bytes' => fake()->numberBetween(200_000, 3_000_000),
            'duration_seconds' => fake()->numberBetween(30, 600),
            'storage_disk' => 'local',
            'storage_path' => 'recordings/'.Str::uuid().'.'.$extension,
            'original_name' => 'rekaman-'.$extension,
            'captured_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'transcript' => null,
        ];
    }

    public function withTranscript(): static
    {
        return $this->state(fn (): array => [
            'transcript' => 'Demam dan batuk sejak tiga hari, tidak ada sesak napas.',
        ]);
    }

    public function fromDevice(): static
    {
        return $this->state(fn (): array => [
            'source' => RecordingSource::Esp32,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'deleted_at' => now(),
        ]);
    }

    /**
     * Rekamannya benar-benar ada di disk, sehingga alur pemutaran dan
     * pengarsipan bisa diuji tanpa menyimpan berkas sungguhan.
     */
    public function withFile(?string $contents = null): static
    {
        return $this->afterCreating(function (EncounterRecording $recording) use ($contents): void {
            Storage::disk($recording->storage_disk)->put(
                $recording->storage_path,
                $contents ?? Str::random(2048),
            );
        });
    }

    /**
     * Berkas sementara yang siap diunggah.
     */
    public static function audioFile(?string $name = null): UploadedFile
    {
        return UploadedFile::fake()->create($name ?? 'rekaman.webm', 512, 'audio/webm');
    }
}
