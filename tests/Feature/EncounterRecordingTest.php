<?php

namespace Tests\Feature;

use App\Enums\EncounterStatus;
use App\Enums\RecordingSource;
use App\Models\Encounter;
use App\Models\EncounterRecording;
use App\Models\Patient;
use App\Models\User;
use Database\Factories\EncounterRecordingFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EncounterRecordingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Rekaman memang disimpan di disk privat, jadi test memakai salinan
         * sementara supaya berkas sungguhan tidak ikut tertimpa.
         */
        Storage::fake('local');
    }

    /**
     * Kunjungan yang sudah ditangani oleh petugas tertentu.
     */
    private function encounterFor(User $doctor, array $attributes = []): Encounter
    {
        return Encounter::factory()->handledBy($doctor)->create($attributes);
    }

    public function test_recording_pages_require_authentication(): void
    {
        $encounter = $this->encounterFor(User::factory()->create());

        $this->get(route('patients.encounters.recordings.index', [$encounter->patient, $encounter]))
            ->assertRedirect('/login');

        $this->post(
            route('patients.encounters.recordings.store', [$encounter->patient, $encounter]),
            ['audio' => UploadedFile::fake()->create('rekaman.webm', 128, 'audio/webm')],
        )->assertRedirect('/login');
    }

    public function test_the_handling_staff_can_open_the_recorder(): void
    {
        $encounter = $this->encounterFor(User::factory()->create());

        $this->actingAs($encounter->doctor)
            ->get(route('patients.encounters.recordings.index', [$encounter->patient, $encounter]))
            ->assertOk()
            ->assertSee('Rekam kondisi')
            ->assertSee('Mulai rekam');
    }

    public function test_staff_who_did_not_handle_the_encounter_is_denied(): void
    {
        $encounter = $this->encounterFor(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->get(route('patients.encounters.recordings.index', [$encounter->patient, $encounter]))
            ->assertForbidden();
    }

    public function test_admin_can_open_any_recorder(): void
    {
        $encounter = $this->encounterFor(User::factory()->create());

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('patients.encounters.recordings.index', [$encounter->patient, $encounter]))
            ->assertOk();
    }

    public function test_audio_is_stored_on_the_private_disk_and_registered(): void
    {
        Storage::fake('local');

        $encounter = $this->encounterFor(User::factory()->create(), [
            'status' => EncounterStatus::Berjalan,
        ]);

        $response = $this->actingAs($encounter->doctor)->post(
            route('patients.encounters.recordings.store', [$encounter->patient, $encounter]),
            [
                'audio' => UploadedFile::fake()->create('rekaman.webm', 256, 'audio/webm'),
                'duration_seconds' => 42,
                'transcript' => 'Batuk kering sejak tiga hari.',
            ],
        );

        $response->assertRedirect()->assertSessionHas('success');
        $response->assertSessionHasNoErrors();

        $recording = EncounterRecording::sole();

        $this->assertSame($encounter->getKey(), $recording->encounter_id);
        $this->assertSame($encounter->doctor_id, $recording->recorded_by);
        $this->assertSame(RecordingSource::Browser, $recording->source);
        $this->assertSame(42, $recording->duration_seconds);
        $this->assertSame('Batuk kering sejak tiga hari.', $recording->transcript);
        $this->assertStringStartsWith('recordings/'.$encounter->getKey().'/browser/', $recording->storage_path);

        Storage::disk('local')->assertExists($recording->storage_path);
    }

    public function test_recording_is_rejected_when_the_encounter_has_ended(): void
    {
        Storage::fake('local');

        $encounter = $this->encounterFor(User::factory()->create(), [
            'status' => EncounterStatus::Selesai,
        ]);

        $this->actingAs($encounter->doctor)
            ->post(route('patients.encounters.recordings.store', [$encounter->patient, $encounter]), [
                'audio' => UploadedFile::fake()->create('rekaman.webm', 128, 'audio/webm'),
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, EncounterRecording::count());
    }

    public function test_upload_rejects_a_file_that_is_not_audio(): void
    {
        Storage::fake('local');

        $encounter = $this->encounterFor(User::factory()->create(), [
            'status' => EncounterStatus::Berjalan,
        ]);

        $this->actingAs($encounter->doctor)
            ->post(route('patients.encounters.recordings.store', [$encounter->patient, $encounter]), [
                'audio' => UploadedFile::fake()->create('dokumen.pdf', 128, 'application/pdf'),
            ])
            ->assertSessionHasErrors('audio');

        $this->assertSame(0, EncounterRecording::count());
    }

    /**
     * Rekaman dari peramban sering dilaporkan sebagai jenis kontainer
     * ({@see config('fesr.audio.allowed_mimes')}) meski isinya hanya suara,
     * jadi semuanya harus diterima dan tetap tersimpan utuh.
     */
    #[DataProvider('browserRecordingMimeTypes')]
    public function test_every_browser_recording_container_is_accepted(string $mime, string $fileName): void
    {
        Storage::fake('local');

        $encounter = $this->encounterFor(User::factory()->create(), [
            'status' => EncounterStatus::Berjalan,
        ]);

        $this->actingAs($encounter->doctor)
            ->post(route('patients.encounters.recordings.store', [$encounter->patient, $encounter]), [
                'audio' => UploadedFile::fake()->create($fileName, 128, $mime),
            ])
            ->assertSessionHasNoErrors();

        $recording = EncounterRecording::sole();

        $this->assertSame($mime, $recording->mime_type);
        $this->assertStringStartsWith('recordings/'.$encounter->getKey().'/browser/', $recording->storage_path);

        // Ekstensi diturunkan dari MIME, bukan dari nama yang dikirim klien.
        $this->assertStringNotContainsString($fileName, $recording->storage_path);
        $this->assertTrue($recording->fileExists());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function browserRecordingMimeTypes(): array
    {
        return [
            'chrome dan edge, opus dalam webm' => ['video/webm', 'suara-pasien.webm'],
            'firefox, opus dalam ogg' => ['application/ogg', 'suara-pasien.ogg'],
            'safari, mp4' => ['video/mp4', 'suara-pasien.mp4'],
            'wav' => ['audio/x-wav', 'suara-pasien.wav'],
        ];
    }

    public function test_upload_rejects_a_duration_beyond_ten_minutes(): void
    {
        Storage::fake('local');

        $encounter = $this->encounterFor(User::factory()->create(), [
            'status' => EncounterStatus::Berjalan,
        ]);

        $this->actingAs($encounter->doctor)
            ->post(route('patients.encounters.recordings.store', [$encounter->patient, $encounter]), [
                'audio' => UploadedFile::fake()->create('rekaman.webm', 128, 'audio/webm'),
                'duration_seconds' => 601,
            ])
            ->assertSessionHasErrors('duration_seconds');

        $this->assertSame(0, EncounterRecording::count());
    }

    public function test_the_handling_staff_can_stream_the_audio(): void
    {
        $recording = EncounterRecordingFactory::new()->withFile()->create();

        $this->actingAs($recording->encounter->doctor)
            ->get(route('patients.encounters.recordings.audio', [
                $recording->encounter->patient,
                $recording->encounter,
                $recording,
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', $recording->mime_type);
    }

    public function test_streaming_is_denied_to_uninvolved_staff(): void
    {
        $recording = EncounterRecordingFactory::new()->withFile()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('patients.encounters.recordings.audio', [
                $recording->encounter->patient,
                $recording->encounter,
                $recording,
            ]))
            ->assertForbidden();
    }

    public function test_a_recording_cannot_be_archived_while_the_encounter_is_running(): void
    {
        $recording = EncounterRecordingFactory::new()->withFile()->create();

        $this->actingAs($recording->encounter->doctor)
            ->delete(route('patients.encounters.recordings.destroy', [
                $recording->encounter->patient,
                $recording->encounter,
                $recording,
            ]))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($recording);
    }

    public function test_an_archived_recording_can_be_restored(): void
    {
        $recording = EncounterRecordingFactory::new()->withFile()->create([
            'encounter_id' => Encounter::factory()
                ->handledBy(User::factory()->create())
                ->completed()
                ->create()
                ->getKey(),
        ]);

        $this->actingAs($recording->encounter->doctor)
            ->delete(route('patients.encounters.recordings.destroy', [
                $recording->encounter->patient,
                $recording->encounter,
                $recording,
            ]))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($recording);

        $this->actingAs($recording->encounter->doctor)
            ->post(route('patients.encounters.recordings.restore', [
                $recording->encounter->patient,
                $recording->encounter,
                $recording,
            ]))
            ->assertSessionHas('success');

        $this->assertNotSoftDeleted($recording);
    }

    public function test_transcript_can_be_saved_by_the_handling_staff(): void
    {
        $recording = EncounterRecordingFactory::new()->create();

        $this->actingAs($recording->encounter->doctor)
            ->patch(route('patients.encounters.recordings.transcript.update', [
                $recording->encounter->patient,
                $recording->encounter,
                $recording,
            ]), ['transcript' => 'Pasien röksing ringan.'])
            ->assertSessionHas('success');

        $this->assertSame('Pasien röksing ringan.', $recording->fresh()->transcript);
    }

    public function test_transcript_is_not_editable_by_uninvolved_staff(): void
    {
        $recording = EncounterRecordingFactory::new()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('patients.encounters.recordings.transcript.update', [
                $recording->encounter->patient,
                $recording->encounter,
                $recording,
            ]), ['transcript' => 'Tidak boleh masuk.'])
            ->assertForbidden();

        $this->assertNull($recording->fresh()->transcript);
    }

    public function test_a_recording_is_flagged_when_its_file_is_missing(): void
    {
        $recording = EncounterRecordingFactory::new()->create();

        $this->actingAs($recording->encounter->doctor)
            ->get(route('patients.encounters.recordings.index', [
                $recording->encounter->patient,
                $recording->encounter,
            ]))
            ->assertOk()
            ->assertSee('Berkas rekaman tidak ditemukan di penyimpanan');
    }

    public function test_archived_recordings_stay_visible_on_the_page(): void
    {
        $encounter = $this->encounterFor(User::factory()->create());

        EncounterRecordingFactory::new()->archived()->create([
            'encounter_id' => $encounter->getKey(),
            'recorded_by' => $encounter->doctor_id,
        ]);

        $this->actingAs($encounter->doctor)
            ->get(route('patients.encounters.recordings.index', [$encounter->patient, $encounter]))
            ->assertOk()
            ->assertSee('Arsip (1)');
    }

    public function test_archived_recordings_still_carry_their_transcript(): void
    {
        $recording = EncounterRecordingFactory::new()->withTranscript()->archived()->create();

        $this->assertSame(
            'Demam dan batuk sejak tiga hari, tidak ada sesak napas.',
            $recording->fresh()->transcript,
        );
    }

    public function test_the_recorder_is_hidden_once_the_encounter_has_ended(): void
    {
        $encounter = $this->encounterFor(User::factory()->create(), [
            'status' => EncounterStatus::Selesai,
        ]);

        $this->actingAs($encounter->doctor)
            ->get(route('patients.encounters.recordings.index', [$encounter->patient, $encounter]))
            ->assertOk()
            ->assertSee('tidak bisa ditambahkan')
            ->assertDontSee('Mulai rekam');
    }

    public function test_the_patient_page_links_to_each_encounter_recorder(): void
    {
        $encounter = $this->encounterFor(User::factory()->create());

        $this->actingAs($encounter->doctor)
            ->get(route('patients.show', $encounter->patient))
            ->assertOk()
            ->assertSee(route('patients.encounters.recordings.index', [$encounter->patient, $encounter]), escape: false);
    }

    public function test_a_recording_belonging_to_another_encounter_is_not_reachable(): void
    {
        $recording = EncounterRecordingFactory::new()->withFile()->create();
        $otherEncounter = $this->encounterFor(User::factory()->admin()->create());

        $this->actingAs($otherEncounter->doctor)
            ->get(route('patients.encounters.recordings.audio', [
                $otherEncounter->patient,
                $otherEncounter,
                $recording,
            ]))
            ->assertNotFound();
    }

    public function test_an_encounter_without_recordings_shows_an_empty_state(): void
    {
        $encounter = $this->encounterFor(User::factory()->create());

        $this->actingAs($encounter->doctor)
            ->get(route('patients.encounters.recordings.index', [$encounter->patient, $encounter]))
            ->assertOk()
            ->assertSee('Belum ada rekaman');
    }

    public function test_a_recording_is_reachable_from_its_own_patient_page(): void
    {
        $recording = EncounterRecordingFactory::new()->withFile()->create();

        $this->actingAs($recording->encounter->doctor)
            ->get(route('patients.encounters.recordings.index', [
                $recording->encounter->patient,
                $recording->encounter,
            ]))
            ->assertOk()
            ->assertSee('Rekaman tersimpan');
    }

    public function test_the_number_of_recordings_is_shown_on_the_patient_page(): void
    {
        $encounter = $this->encounterFor(User::factory()->create());

        EncounterRecordingFactory::new()->count(2)->create([
            'encounter_id' => $encounter->getKey(),
            'recorded_by' => $encounter->doctor_id,
        ]);

        $patient = Patient::find($encounter->patient_id);

        $this->actingAs($encounter->doctor)
            ->get(route('patients.show', $patient))
            ->assertOk()
            ->assertSee('2 rekaman');
    }
}
