<?php

namespace Tests\Feature;

use App\Enums\EncounterStatus;
use App\Enums\RecordingSource;
use App\Models\Encounter;
use App\Models\EncounterRecording;
use App\Models\Esp32Device;
use App\Models\User;
use Database\Factories\EncounterRecordingFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class Esp32DeviceRecordingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    /**
     * Token yang cocok dengan perangkat yang dibuat.
     */
    private function issueDevice(?User $owner = null, bool $active = true): array
    {
        $token = Str::random(64);

        $device = Esp32Device::create([
            'name' => 'Perekam Poli Dalam',
            'user_id' => ($owner ?? User::factory()->create())->getKey(),
            'token_hash' => hash('sha256', $token),
            'is_active' => $active,
        ]);

        return [$device, $token];
    }

    public function test_a_request_without_a_token_is_rejected(): void
    {
        $encounter = Encounter::factory()->handledBy(User::factory()->create())->create();

        $this->postJson(route('api.encounters.recordings.store', $encounter), [
            'audio' => UploadedFile::fake()->create('rekaman.wav', 128, 'audio/wav'),
        ])->assertUnauthorized();
    }

    public function test_an_unknown_token_is_rejected(): void
    {
        $encounter = Encounter::factory()->handledBy(User::factory()->create())->create();

        $this->withHeader('X-Device-Token', 'token-yang-salah')
            ->postJson(route('api.encounters.recordings.store', $encounter), [
                'audio' => UploadedFile::fake()->create('rekaman.wav', 128, 'audio/wav'),
            ])
            ->assertUnauthorized();
    }

    public function test_a_disabled_device_is_rejected(): void
    {
        [$device, $token] = $this->issueDevice(active: false);
        $encounter = Encounter::factory()->handledBy($device->user)->create();

        $this->withHeader('X-Device-Token', $token)
            ->postJson(route('api.encounters.recordings.store', $encounter), [
                'audio' => UploadedFile::fake()->create('rekaman.wav', 128, 'audio/wav'),
            ])
            ->assertForbidden();
    }

    public function test_a_device_belonging_to_an_inactive_owner_is_rejected(): void
    {
        [$device, $token] = $this->issueDevice();
        User::whereKey($device->user_id)->update(['is_active' => false]);
        $encounter = Encounter::factory()->handledBy($device->user)->create();

        $this->withHeader('X-Device-Token', $token)
            ->postJson(route('api.encounters.recordings.store', $encounter), [
                'audio' => UploadedFile::fake()->create('rekaman.wav', 128, 'audio/wav'),
            ])
            ->assertForbidden();
    }

    public function test_a_device_can_confirm_its_owner(): void
    {
        [$device, $token] = $this->issueDevice();

        $this->withHeader('X-Device-Token', $token)
            ->getJson(route('api.device.show'))
            ->assertOk()
            ->assertJsonPath('device.id', $device->getKey())
            ->assertJsonPath('owner.id', $device->user_id);
    }

    public function test_a_device_can_send_a_recording_for_its_owner_s_encounter(): void
    {
        Storage::fake('local');

        [$device, $token] = $this->issueDevice();
        $encounter = Encounter::factory()->handledBy($device->user)->create([
            'status' => EncounterStatus::Berjalan,
        ]);

        $response = $this->withHeader('X-Device-Token', $token)
            ->postJson(route('api.encounters.recordings.store', $encounter), [
                'audio' => UploadedFile::fake()->create('rekaman.wav', 512, 'audio/wav'),
                'duration_seconds' => 90,
            ]);

        $response->assertCreated()->assertJsonPath('message', 'Rekaman tersimpan.');

        $recording = EncounterRecording::sole();

        $this->assertSame(RecordingSource::Esp32, $recording->source);
        $this->assertSame($device->user_id, $recording->recorded_by);
        $this->assertSame(90, $recording->duration_seconds);
        $this->assertStringStartsWith('recordings/'.$encounter->getKey().'/esp32/', $recording->storage_path);
    }

    public function test_a_device_cannot_send_a_recording_for_another_staff_s_encounter(): void
    {
        Storage::fake('local');

        [$device, $token] = $this->issueDevice();
        $encounter = Encounter::factory()->handledBy(User::factory()->create())->create([
            'status' => EncounterStatus::Berjalan,
        ]);

        $this->withHeader('X-Device-Token', $token)
            ->postJson(route('api.encounters.recordings.store', $encounter), [
                'audio' => UploadedFile::fake()->create('rekaman.wav', 128, 'audio/wav'),
            ])
            ->assertForbidden();

        $this->assertSame(0, EncounterRecording::count());
    }

    public function test_a_device_cannot_send_a_recording_to_a_finished_encounter(): void
    {
        Storage::fake('local');

        [$device, $token] = $this->issueDevice();
        $encounter = Encounter::factory()->handledBy($device->user)->create([
            'status' => EncounterStatus::Selesai,
        ]);

        $this->withHeader('X-Device-Token', $token)
            ->postJson(route('api.encounters.recordings.store', $encounter), [
                'audio' => UploadedFile::fake()->create('rekaman.wav', 128, 'audio/wav'),
            ])
            ->assertStatus(409);

        $this->assertSame(0, EncounterRecording::count());
    }

    public function test_a_device_recording_is_checked_by_the_same_rules_as_a_browser_one(): void
    {
        Storage::fake('local');

        [$device, $token] = $this->issueDevice();
        $encounter = Encounter::factory()->handledBy($device->user)->create([
            'status' => EncounterStatus::Berjalan,
        ]);

        $this->withHeader('X-Device-Token', $token)
            ->postJson(route('api.encounters.recordings.store', $encounter), [
                'audio' => UploadedFile::fake()->create('dokumen.pdf', 128, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('audio');
    }

    public function test_a_successful_request_marks_the_device_as_seen(): void
    {
        [$device, $token] = $this->issueDevice();

        $this->assertNull($device->last_seen_at);

        $this->withHeader('X-Device-Token', $token)
            ->getJson(route('api.device.show'))
            ->assertOk();

        $this->assertNotNull($device->fresh()->last_seen_at);
    }

    public function test_only_admin_can_reach_the_device_management_page(): void
    {
        $this->get(route('admin.devices.index'))->assertRedirect('/login');

        $this->actingAs(User::factory()->create())->get(route('admin.devices.index'))->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.devices.index'))
            ->assertOk()
            ->assertSee('Perangkat perekam');
    }

    public function test_admin_can_register_a_device_and_the_token_is_shown_once(): void
    {
        $owner = User::factory()->create(['name' => 'Dokter Sari']);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.devices.store'), ['name' => 'Perekam IGD', 'user_id' => $owner->getKey()]);

        $response->assertRedirect(route('admin.devices.index'));
        $response->assertSessionHas('success');

        $token = $response->baseResponse->getSession()->get('device_token');

        $this->assertIsString($token);
        $this->assertSame(64, strlen($token));

        $device = Esp32Device::sole();

        $this->assertSame('Perekam IGD', $device->name);
        $this->assertSame($owner->getKey(), $device->user_id);

        /*
         * Yang disimpan hanya hash-nya. Kalau token-nya ikut tersimpan,
         * bocornya isi tabel ini sudah cukup untuk mengunggah.
         */
        $this->assertNotSame($token, $device->token_hash);
        $this->assertSame(hash('sha256', $token), $device->token_hash);
    }

    public function test_the_new_token_works_for_the_next_request(): void
    {
        [, $token] = $this->issueDevice();

        $this->withHeader('X-Device-Token', $token)->getJson(route('api.device.show'))->assertOk();
    }

    public function test_regenerating_a_token_invalidates_the_previous_one(): void
    {
        $admin = User::factory()->admin()->create();
        [$device, $oldToken] = $this->issueDevice();

        $response = $this->actingAs($admin)
            ->post(route('admin.devices.token.store', $device));

        $response->assertSessionHas('success');

        $newToken = $response->baseResponse->getSession()->get('device_token');

        $this->assertNotSame($oldToken, $newToken);

        $this->withHeader('X-Device-Token', $oldToken)->getJson(route('api.device.show'))->assertUnauthorized();
        $this->withHeader('X-Device-Token', $newToken)->getJson(route('api.device.show'))->assertOk();
    }

    public function test_a_device_can_be_switched_off_and_on_again(): void
    {
        $admin = User::factory()->admin()->create();
        [$device, $token] = $this->issueDevice();

        $this->actingAs($admin)->post(route('admin.devices.toggle', $device));
        $this->assertFalse($device->fresh()->is_active);

        $this->withHeader('X-Device-Token', $token)->getJson(route('api.device.show'))->assertForbidden();

        $this->actingAs($admin)->post(route('admin.devices.toggle', $device));
        $this->assertTrue($device->fresh()->is_active);

        $this->withHeader('X-Device-Token', $token)->getJson(route('api.device.show'))->assertOk();
    }

    public function test_a_device_can_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        [$device, $token] = $this->issueDevice();

        $this->actingAs($admin)->delete(route('admin.devices.destroy', $device));

        $this->assertSame(0, Esp32Device::count());

        $this->withHeader('X-Device-Token', $token)->getJson(route('api.device.show'))->assertUnauthorized();
    }

    public function test_a_device_belongs_to_a_staff_member(): void
    {
        $owner = User::factory()->create();
        [$device] = $this->issueDevice($owner);

        $this->assertTrue($owner->esp32Devices->contains($device));
    }

    public function test_a_device_token_cannot_be_used_on_the_web_pages(): void
    {
        $recording = EncounterRecordingFactory::new()->withFile()->create();

        [, $token] = $this->issueDevice();

        $this->withHeader('X-Device-Token', $token)
            ->getJson(route('patients.encounters.recordings.audio', [
                $recording->encounter->patient,
                $recording->encounter,
                $recording,
            ]))
            ->assertUnauthorized();
    }

    public function test_the_owner_cannot_read_the_audio_endpoint_with_a_token(): void
    {
        $recording = EncounterRecordingFactory::new()->withFile()->create();

        [, $token] = $this->issueDevice($recording->encounter->doctor);

        $this->withHeader('X-Device-Token', $token)
            ->getJson(route('patients.encounters.recordings.audio', [
                $recording->encounter->patient,
                $recording->encounter,
                $recording,
            ]))
            ->assertUnauthorized();
    }
}
