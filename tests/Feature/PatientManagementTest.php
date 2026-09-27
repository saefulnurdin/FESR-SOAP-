<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPatientData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Dokter Sari',
            'gender' => Gender::Perempuan->value,
            'birth_date' => '1992-04-11',
            'birth_place' => 'Bandung',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 10',
        ], $overrides);
    }

    public function test_patient_list_is_only_available_to_authenticated_users(): void
    {
        $this->get('/patients')->assertRedirect('/login');
    }

    public function test_any_active_user_can_manage_patients(): void
    {
        $staff = User::factory()->create();
        $patient = Patient::factory()->create(['name' => 'Dokter Budi']);

        $this->actingAs($staff)->get('/patients')->assertOk()->assertSee($patient->name);
        $this->actingAs($staff)->get('/patients/create')->assertOk();
        $this->actingAs($staff)->get(route('patients.show', $patient))->assertOk()->assertSee($patient->name);
    }

    public function test_patient_list_can_be_searched(): void
    {
        $user = User::factory()->create();
        Patient::factory()->create(['name' => 'Dokter Budi']);
        $sari = Patient::factory()->create([
            'name' => 'Dokter Sari',
            'nik' => '3273010505920001',
        ]);

        $this->actingAs($user)
            ->get('/patients?search=Sari')
            ->assertOk()
            ->assertSee($sari->name)
            ->assertDontSee('Dokter Budi');

        $this->actingAs($user)
            ->get('/patients?search='.$sari->medical_record_number)
            ->assertOk()
            ->assertSee($sari->name)
            ->assertDontSee('Dokter Budi');

        $this->actingAs($user)
            ->get('/patients?search=3273010505920001')
            ->assertOk()
            ->assertSee($sari->name)
            ->assertDontSee('Dokter Budi');
    }

    public function test_patient_list_can_be_filtered_by_gender(): void
    {
        $user = User::factory()->create();
        Patient::factory()->create(['name' => 'Dokter Budi', 'gender' => Gender::LakiLaki]);
        Patient::factory()->create(['name' => 'Dokter Sari', 'gender' => Gender::Perempuan]);

        $this->actingAs($user)
            ->get('/patients?gender=P')
            ->assertOk()
            ->assertSee('Dokter Sari')
            ->assertDontSee('Dokter Budi');
    }

    public function test_patient_is_registered_with_a_generated_medical_record_number(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/patients', $this->validPatientData());

        $patient = Patient::sole();

        $response->assertRedirect(route('patients.show', $patient));
        $response->assertSessionHas('success');

        $this->assertSame('Dokter Sari', $patient->name);
        $this->assertSame(Gender::Perempuan, $patient->gender);
        $this->assertSame('1992-04-11', $patient->birth_date->toDateString());
        $this->assertMatchesRegularExpression('/^MRN-\d{4}-\d{6}$/', $patient->medical_record_number);
    }

    public function test_medical_record_number_is_unique_for_every_patient(): void
    {
        Patient::factory()->count(3)->create();

        $numbers = Patient::pluck('medical_record_number')->all();

        $this->assertCount(3, array_unique($numbers));
        $this->assertNotContains(null, $numbers);
    }

    public function test_patient_can_be_registered_without_a_nik(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/patients', $this->validPatientData([
            'nik' => '',
            'gender' => Gender::LakiLaki->value,
        ]));

        $this->assertNull(Patient::sole()->nik);
    }

    public function test_patient_registration_requires_a_name_and_a_valid_gender(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/patients', ['gender' => 'X'])
            ->assertSessionHasErrors(['name', 'gender']);

        $this->assertSame(0, Patient::count());
    }

    public function test_nik_must_be_sixteen_digits_and_unique(): void
    {
        $user = User::factory()->create();
        Patient::factory()->create(['nik' => '3273010505920001']);

        $this->actingAs($user)
            ->post('/patients', $this->validPatientData(['nik' => '123']))
            ->assertSessionHasErrors('nik');

        $this->actingAs($user)
            ->post('/patients', $this->validPatientData(['nik' => '3273010505920001']))
            ->assertSessionHasErrors('nik');

        $this->actingAs($user)
            ->post('/patients', $this->validPatientData(['nik' => '3273010505999999']))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Patient::count());
    }

    public function test_patient_data_is_updated(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create(['name' => 'Dokter Budi']);

        $response = $this->actingAs($user)->put(route('patients.update', $patient), [
            'name' => 'Dokter Budi Santoso',
            'gender' => Gender::LakiLaki->value,
            'phone' => '081298765432',
        ]);

        $response->assertRedirect(route('patients.show', $patient));
        $response->assertSessionHasNoErrors();

        $patient->refresh();

        $this->assertSame('Dokter Budi Santoso', $patient->name);
        $this->assertSame('081298765432', $patient->phone);
    }

    public function test_patient_can_keep_their_own_nik_when_updated(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create([
            'nik' => '3273010505920001',
            'name' => 'Dokter Budi',
            'gender' => Gender::LakiLaki->value,
        ]);

        $this->actingAs($user)->put(route('patients.update', $patient), [
            'nik' => '3273010505920001',
            'name' => 'Dokter Budi',
            'gender' => Gender::LakiLaki->value,
        ])->assertSessionHasNoErrors();

        $this->assertSame('3273010505920001', $patient->refresh()->nik);
    }

    public function test_medical_record_number_cannot_be_changed(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create(['name' => 'Dokter Budi']);
        $original = $patient->medical_record_number;

        $this->actingAs($user)->put(route('patients.update', $patient), [
            'name' => 'Dokter Budi Santoso',
            'gender' => Gender::LakiLaki->value,
            'medical_record_number' => 'MRN-9999-999999',
        ]);

        $this->assertSame($original, $patient->refresh()->medical_record_number);
    }

    public function test_patient_is_archived_without_losing_the_encounter_history(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create(['name' => 'Dokter Budi']);
        Encounter::factory()->count(2)->create(['patient_id' => $patient->getKey()]);

        $response = $this->actingAs($user)->delete(route('patients.destroy', $patient));

        $response->assertRedirect(route('patients.index'));

        $this->assertSoftDeleted($patient);
        $this->assertSame(0, Patient::count());
        $this->assertSame(2, Encounter::withTrashed()->where('patient_id', $patient->getKey())->count());
    }

    public function test_archived_patient_is_hidden_from_the_default_list(): void
    {
        $user = User::factory()->create();
        $archived = Patient::factory()->create(['name' => 'Dokter Budi']);
        $archived->delete();
        Patient::factory()->create(['name' => 'Dokter Sari']);

        $this->actingAs($user)
            ->get('/patients')
            ->assertOk()
            ->assertSee('Dokter Sari')
            ->assertDontSee('Dokter Budi');

        $this->actingAs($user)
            ->get('/patients?archived=1')
            ->assertOk()
            ->assertSee('Dokter Budi');
    }

    public function test_archived_patient_can_be_restored(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create(['name' => 'Dokter Budi']);
        $patient->delete();

        $response = $this->actingAs($user)->post(route('patients.restore', $patient));

        $response->assertRedirect(route('patients.show', $patient));

        $this->assertNotSoftDeleted($patient);
        $this->assertSame(1, Patient::count());
    }

    public function test_archived_patient_page_is_not_reachable(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        $patient->delete();

        $this->actingAs($user)->get(route('patients.show', $patient))->assertNotFound();
    }

    public function test_inactive_user_cannot_reach_patient_data(): void
    {
        $patient = Patient::factory()->create(['name' => 'Dokter Budi']);

        $this->actingAs(User::factory()->inactive()->create())
            ->get('/patients')
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->inactive()->create())
            ->get(route('patients.show', $patient))
            ->assertRedirect(route('login'));
    }
}
