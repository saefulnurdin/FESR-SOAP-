<?php

namespace Tests\Feature;

use App\Enums\EncounterStatus;
use App\Enums\VisitType;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncounterManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validEncounterData(array $overrides = []): array
    {
        return array_merge([
            'occurred_at' => '2026-09-27T09:30',
            'visit_type' => VisitType::RawatJalan->value,
            'status' => EncounterStatus::Berjalan->value,
            'chief_complaint' => 'Demam dan batuk selama tiga hari',
        ], $overrides);
    }

    public function test_encounter_pages_are_only_available_to_authenticated_users(): void
    {
        $patient = Patient::factory()->create();

        $this->get(route('patients.encounters.create', $patient))->assertRedirect('/login');
        $this->post(route('patients.encounters.store', $patient), $this->validEncounterData())
            ->assertRedirect('/login');
    }

    public function test_encounter_is_recorded_for_the_patient_by_the_signed_in_user(): void
    {
        $user = User::factory()->create(['name' => 'Dokter Sari']);
        $patient = Patient::factory()->create(['name' => 'Dokter Budi']);

        $response = $this->actingAs($user)->post(
            route('patients.encounters.store', $patient),
            $this->validEncounterData()
        );

        $encounter = Encounter::sole();

        $response->assertRedirect(route('patients.show', $patient));
        $response->assertSessionHasNoErrors();

        $this->assertSame($patient->getKey(), $encounter->patient_id);
        $this->assertSame($user->getKey(), $encounter->doctor_id);
        $this->assertSame(EncounterStatus::Berjalan, $encounter->status);
        $this->assertSame(VisitType::RawatJalan, $encounter->visit_type);
        $this->assertSame('Dokter Sari', $encounter->doctor->name);
    }

    public function test_doctor_cannot_be_forged_through_the_form(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $patient = Patient::factory()->create();

        $this->actingAs($user)->post(
            route('patients.encounters.store', $patient),
            $this->validEncounterData(['doctor_id' => $other->getKey()])
        );

        $this->assertSame($user->getKey(), Encounter::sole()->doctor_id);
    }

    public function test_patient_id_cannot_be_forged_through_the_form(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $other = Patient::factory()->create();

        $this->actingAs($user)->post(
            route('patients.encounters.store', $patient),
            $this->validEncounterData(['patient_id' => $other->getKey()])
        );

        $this->assertSame($patient->getKey(), Encounter::sole()->patient_id);
    }

    public function test_encounter_requires_a_valid_time_visit_type_and_status(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $this->actingAs($user)
            ->post(route('patients.encounters.store', $patient), [])
            ->assertSessionHasErrors(['occurred_at', 'visit_type', 'status']);

        $this->actingAs($user)
            ->post(route('patients.encounters.store', $patient), $this->validEncounterData([
                'visit_type' => 'teleconference',
                'status' => 'unknown',
            ]))
            ->assertSessionHasErrors(['visit_type', 'status']);

        $this->assertSame(0, Encounter::count());
    }

    public function test_encounter_defaults_to_an_on_going_visit(): void
    {
        $this->assertSame(EncounterStatus::Berjalan, (new Encounter)->status);
    }

    public function test_encounter_is_listed_in_the_patient_history(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create(['name' => 'Dokter Budi']);
        $encounter = Encounter::factory()->create([
            'patient_id' => $patient->getKey(),
            'chief_complaint' => 'Keluhan khas pasien',
        ]);

        $this->actingAs($user)
            ->get(route('patients.show', $patient))
            ->assertOk()
            ->assertSee($encounter->chief_complaint)
            ->assertSee($encounter->visit_type->label())
            ->assertSee($encounter->status->label());
    }

    public function test_encounter_is_updated(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $encounter = Encounter::factory()->create([
            'patient_id' => $patient->getKey(),
            'status' => EncounterStatus::Berjalan,
        ]);

        $response = $this->actingAs($user)->put(
            route('patients.encounters.update', [$patient, $encounter]),
            $this->validEncounterData([
                'status' => EncounterStatus::Selesai->value,
                'chief_complaint' => 'Keluhan telah diperbarui',
            ])
        );

        $response->assertRedirect(route('patients.show', $patient));
        $response->assertSessionHasNoErrors();

        $encounter->refresh();

        $this->assertSame(EncounterStatus::Selesai, $encounter->status);
        $this->assertSame('Keluhan telah diperbarui', $encounter->chief_complaint);
    }

    public function test_encounter_is_archived(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $encounter = Encounter::factory()->create(['patient_id' => $patient->getKey()]);

        $response = $this->actingAs($user)->delete(
            route('patients.encounters.destroy', [$patient, $encounter])
        );

        $response->assertRedirect(route('patients.show', $patient));

        $this->assertSoftDeleted($encounter);
        $this->assertSame(0, Encounter::count());
    }

    public function test_encounter_of_another_patient_cannot_be_reached(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $other = Patient::factory()->create();
        $encounter = Encounter::factory()->create(['patient_id' => $other->getKey()]);

        $this->actingAs($user)
            ->get(route('patients.encounters.edit', [$patient, $encounter]))
            ->assertNotFound();

        $this->actingAs($user)
            ->put(route('patients.encounters.update', [$patient, $encounter]), $this->validEncounterData())
            ->assertNotFound();

        $this->actingAs($user)
            ->delete(route('patients.encounters.destroy', [$patient, $encounter]))
            ->assertNotFound();
    }

    public function test_encounter_cannot_be_added_to_an_archived_patient(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $patient->delete();

        $this->actingAs($user)
            ->get(route('patients.encounters.create', $patient))
            ->assertNotFound();

        $this->actingAs($user)
            ->post(route('patients.encounters.store', $patient), $this->validEncounterData())
            ->assertNotFound();

        $this->assertSame(0, Encounter::withTrashed()->count());
    }

    public function test_inactive_user_cannot_reach_encounter_data(): void
    {
        $patient = Patient::factory()->create();
        $encounter = Encounter::factory()->create(['patient_id' => $patient->getKey()]);

        $this->actingAs(User::factory()->inactive()->create())
            ->get(route('patients.encounters.edit', [$patient, $encounter]))
            ->assertRedirect(route('login'));
    }
}
