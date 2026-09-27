<?php

namespace Tests\Feature;

use App\Enums\DocumentFieldType;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateField;
use App\Models\DocumentTemplateSection;
use App\Models\DocumentType;
use App\Models\User;
use Database\Seeders\SoapTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTemplateManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->staff = User::factory()->create();
    }

    /*
     * ---------------------------------------------------------------------
     * Akses
     * ---------------------------------------------------------------------
     */

    public function test_guest_is_redirected_from_document_type_pages(): void
    {
        $this->get(route('admin.document-types.index'))->assertRedirect(route('login'));
    }

    public function test_staff_cannot_manage_document_templates(): void
    {
        $this->actingAs($this->staff);

        $this->get(route('admin.document-types.index'))->assertForbidden();
    }

    public function test_admin_can_open_document_type_pages(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('admin.document-types.index'))->assertOk();
        $this->get(route('admin.document-types.create'))->assertOk();
    }

    /*
     * ---------------------------------------------------------------------
     * Jenis dokumen
     * ---------------------------------------------------------------------
     */

    public function test_admin_can_create_document_type(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.document-types.store'), [
            'code' => 'SOAP',
            'name' => 'Catatan SOAP',
            'description' => 'Catatan kunjungan',
            'is_active' => '1',
        ])->assertRedirect(route('admin.document-types.index'));

        $this->assertDatabaseHas('document_types', [
            'code' => 'soap',
            'name' => 'Catatan SOAP',
            'is_active' => true,
        ]);
    }

    public function test_document_type_requires_unique_code(): void
    {
        $this->actingAs($this->admin);

        DocumentType::factory()->create(['code' => 'soap']);

        $this->from(route('admin.document-types.create'))->post(route('admin.document-types.store'), [
            'code' => 'SOAP',
            'name' => 'Duplikat',
            'is_active' => '1',
        ])->assertRedirect(route('admin.document-types.create'))
            ->assertSessionHasErrors(['code' => 'Kode jenis dokumen sudah digunakan.']);

        $this->assertDatabaseCount('document_types', 1);
    }

    public function test_document_type_rejects_code_with_invalid_characters(): void
    {
        $this->actingAs($this->admin);

        $this->from(route('admin.document-types.create'))->post(route('admin.document-types.store'), [
            'code' => 'catatan soap!',
            'name' => 'Catatan SOAP',
            'is_active' => '1',
        ])->assertRedirect(route('admin.document-types.create'));

        $this->assertDatabaseCount('document_types', 0);
    }

    public function test_admin_can_update_document_type(): void
    {
        $this->actingAs($this->admin);

        $documentType = DocumentType::factory()->create(['name' => 'Nama lama']);

        $this->put(route('admin.document-types.update', $documentType), [
            'code' => $documentType->code,
            'name' => 'Nama baru',
            'is_active' => '0',
        ])->assertRedirect(route('admin.document-types.index'));

        $this->assertDatabaseHas('document_types', [
            'id' => $documentType->id,
            'name' => 'Nama baru',
            'is_active' => false,
        ]);
    }

    public function test_document_type_with_templates_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin);

        $documentType = DocumentType::factory()->has(DocumentTemplate::factory(), 'templates')->create();

        $this->from(route('admin.document-types.index'))->delete(route('admin.document-types.destroy', $documentType))
            ->assertRedirect(route('admin.document-types.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('document_types', ['id' => $documentType->id]);
    }

    public function test_empty_document_type_can_be_deleted(): void
    {
        $this->actingAs($this->admin);

        $documentType = DocumentType::factory()->create();

        $this->delete(route('admin.document-types.destroy', $documentType))
            ->assertRedirect(route('admin.document-types.index'));

        $this->assertDatabaseMissing('document_types', ['id' => $documentType->id]);
    }

    /*
     * ---------------------------------------------------------------------
     * Template
     * ---------------------------------------------------------------------
     */

    public function test_admin_can_create_template_for_document_type(): void
    {
        $this->actingAs($this->admin);

        $documentType = DocumentType::factory()->create();

        $this->post(route('admin.document-types.templates.store', $documentType), [
            'name' => 'SOAP Dewasa',
            'description' => 'Untuk pasien dewasa',
            'is_active' => '1',
        ])->assertRedirect(route('admin.templates.edit', DocumentTemplate::sole()));

        $this->assertDatabaseHas('document_templates', [
            'document_type_id' => $documentType->id,
            'name' => 'SOAP Dewasa',
            'is_active' => true,
        ]);
    }

    public function test_deleting_template_removes_its_structure(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        DocumentTemplateSection::factory()->has(DocumentTemplateField::factory(), 'fields')->create([
            'document_template_id' => $template->id,
        ]);

        $this->delete(route('admin.templates.destroy', $template))
            ->assertRedirect(route('admin.document-types.templates.index', $template->document_type_id));

        $this->assertDatabaseMissing('document_templates', ['id' => $template->id]);
        $this->assertDatabaseCount('document_template_sections', 0);
        $this->assertDatabaseCount('document_template_fields', 0);
    }

    /*
     * ---------------------------------------------------------------------
     * Bagian
     * ---------------------------------------------------------------------
     */

    public function test_admin_can_add_section_to_template(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();

        $this->post(route('admin.templates.sections.store', $template), [
            'title' => 'Tanda Vital',
            'hint' => 'Pengukuran yang dilakukan petugas',
        ])->assertRedirect(route('admin.templates.edit', $template));

        $this->assertDatabaseHas('document_template_sections', [
            'document_template_id' => $template->id,
            'key' => 'tanda_vital',
            'title' => 'Tanda Vital',
            'sort_order' => 1,
        ]);
    }

    public function test_section_key_is_derived_from_explicitly_given_key(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();

        $this->post(route('admin.templates.sections.store', $template), [
            'title' => 'Tanda Vital',
            'key' => 'Tanda Vital Utama',
        ]);

        $this->assertDatabaseHas('document_template_sections', [
            'document_template_id' => $template->id,
            'key' => 'tanda_vital_utama',
        ]);
    }

    public function test_section_key_must_be_unique_within_a_template(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        DocumentTemplateSection::factory()->create([
            'document_template_id' => $template->id,
            'key' => 'subjective',
        ]);

        $this->from(route('admin.templates.edit', $template))->post(route('admin.templates.sections.store', $template), [
            'title' => 'Subjective',
        ])->assertRedirect(route('admin.templates.edit', $template))
            ->assertSessionHasErrors(['key' => 'Key ini sudah dipakai bagian lain pada template yang sama.']);

        $this->assertSame(1, $template->sections()->count());
    }

    public function test_same_section_key_is_allowed_on_another_template(): void
    {
        $this->actingAs($this->admin);

        DocumentTemplateSection::factory()->create(['key' => 'subjective']);

        $template = DocumentTemplate::factory()->create();

        $this->post(route('admin.templates.sections.store', $template), ['title' => 'Subjective'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('document_template_sections', [
            'document_template_id' => $template->id,
            'key' => 'subjective',
        ]);
    }

    public function test_sections_are_numbered_in_order_of_creation(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();

        $this->post(route('admin.templates.sections.store', $template), ['title' => 'Subjective']);
        $this->post(route('admin.templates.sections.store', $template), ['title' => 'Objective']);
        $this->post(route('admin.templates.sections.store', $template), ['title' => 'Plan']);

        $this->assertSame(
            [1, 2, 3],
            $template->sections()->pluck('sort_order')->all(),
        );
    }

    public function test_deleting_section_also_deletes_its_fields(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $section = DocumentTemplateSection::factory()->has(DocumentTemplateField::factory(), 'fields')->create([
            'document_template_id' => $template->id,
        ]);

        $this->delete(route('admin.templates.sections.destroy', [$template, $section]))
            ->assertRedirect(route('admin.templates.edit', $template));

        $this->assertDatabaseMissing('document_template_sections', ['id' => $section->id]);
        $this->assertDatabaseCount('document_template_fields', 0);
    }

    public function test_section_of_another_template_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $section = DocumentTemplateSection::factory()->create();

        $this->delete(route('admin.templates.sections.destroy', [$template, $section]))
            ->assertNotFound();

        $this->assertDatabaseHas('document_template_sections', ['id' => $section->id]);
    }

    /*
     * ---------------------------------------------------------------------
     * Isian
     * ---------------------------------------------------------------------
     */

    public function test_admin_can_add_field_to_section(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $section = DocumentTemplateSection::factory()->create([
            'document_template_id' => $template->id,
        ]);

        $this->post(route('admin.templates.fields.store', $template), [
            'document_template_section_id' => $section->id,
            'label' => 'Tekanan darah',
            'type' => DocumentFieldType::Text->value,
            'unit' => 'mmHg',
            'is_required' => '1',
        ])->assertRedirect(route('admin.templates.edit', $template));

        $this->assertDatabaseHas('document_template_fields', [
            'document_template_section_id' => $section->id,
            'key' => 'tekanan_darah',
            'label' => 'Tekanan darah',
            'type' => 'text',
            'unit' => 'mmHg',
            'is_required' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_select_field_reads_one_option_per_line(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $section = DocumentTemplateSection::factory()->create([
            'document_template_id' => $template->id,
        ]);

        $this->post(route('admin.templates.fields.store', $template), [
            'document_template_section_id' => $section->id,
            'label' => 'Edema',
            'type' => DocumentFieldType::Select->value,
            'options' => "Ya\n\n  Tidak  \nYa",
            'is_required' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('document_template_fields', [
            'document_template_section_id' => $section->id,
            'type' => 'select',
        ]);

        $this->assertSame(['Ya', 'Tidak'], DocumentTemplateField::sole()->options);
    }

    public function test_select_field_requires_options(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $section = DocumentTemplateSection::factory()->create([
            'document_template_id' => $template->id,
        ]);

        $this->from(route('admin.templates.fields.create', $template))->post(route('admin.templates.fields.store', $template), [
            'document_template_section_id' => $section->id,
            'label' => 'Edema',
            'type' => DocumentFieldType::Select->value,
            'options' => '',
            'is_required' => '0',
        ])->assertRedirect(route('admin.templates.fields.create', $template))
            ->assertSessionHasErrors(['options' => 'Isian pilihan wajib menyertakan minimal satu pilihan.']);

        $this->assertDatabaseCount('document_template_fields', 0);
    }

    public function test_options_are_discarded_for_field_that_does_not_need_them(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $section = DocumentTemplateSection::factory()->create([
            'document_template_id' => $template->id,
        ]);

        $this->post(route('admin.templates.fields.store', $template), [
            'document_template_section_id' => $section->id,
            'label' => 'Keluhan utama',
            'type' => DocumentFieldType::Textarea->value,
            'options' => "Ya\nTidak",
            'is_required' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertNull(DocumentTemplateField::sole()->options);
    }

    public function test_field_key_must_be_unique_within_a_section(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $section = DocumentTemplateSection::factory()->create([
            'document_template_id' => $template->id,
        ]);
        DocumentTemplateField::factory()->create([
            'document_template_section_id' => $section->id,
            'key' => 'tekanan_darah',
        ]);

        $this->from(route('admin.templates.fields.create', $template))->post(route('admin.templates.fields.store', $template), [
            'document_template_section_id' => $section->id,
            'label' => 'Tekanan darah',
            'type' => DocumentFieldType::Text->value,
            'is_required' => '0',
        ])->assertRedirect(route('admin.templates.fields.create', $template))
            ->assertSessionHasErrors(['key' => 'Key ini sudah dipakai isian lain pada bagian yang sama.']);

        $this->assertSame(1, $section->fields()->count());
    }

    public function test_field_cannot_be_added_to_a_section_of_another_template(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $section = DocumentTemplateSection::factory()->create();

        $this->post(route('admin.templates.fields.store', $template), [
            'document_template_section_id' => $section->id,
            'label' => 'Tekanan darah',
            'type' => DocumentFieldType::Text->value,
            'is_required' => '0',
        ])->assertSessionHasErrors('document_template_section_id');

        $this->assertDatabaseCount('document_template_fields', 0);
    }

    public function test_admin_can_move_field_to_another_section(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $first = DocumentTemplateSection::factory()->create(['document_template_id' => $template->id, 'sort_order' => 1]);
        $second = DocumentTemplateSection::factory()->create(['document_template_id' => $template->id, 'sort_order' => 2]);
        $field = DocumentTemplateField::factory()->create([
            'document_template_section_id' => $first->id,
            'type' => DocumentFieldType::Text,
            'sort_order' => 3,
        ]);

        $this->put(route('admin.templates.fields.update', [$template, $field]), [
            'document_template_section_id' => $second->id,
            'label' => $field->label,
            'key' => $field->key,
            'type' => $field->type->value,
            'is_required' => '0',
        ])->assertRedirect(route('admin.templates.edit', $template));

        $this->assertDatabaseHas('document_template_fields', [
            'id' => $field->id,
            'document_template_section_id' => $second->id,
            'sort_order' => 1,
        ]);
    }

    public function test_field_keeps_its_order_when_only_labels_change(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $section = DocumentTemplateSection::factory()->create(['document_template_id' => $template->id]);
        $field = DocumentTemplateField::factory()->create([
            'document_template_section_id' => $section->id,
            'type' => DocumentFieldType::Textarea,
            'sort_order' => 2,
        ]);

        $this->put(route('admin.templates.fields.update', [$template, $field]), [
            'document_template_section_id' => $section->id,
            'label' => 'Nama baru',
            'key' => $field->key,
            'type' => $field->type->value,
            'is_required' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('document_template_fields', [
            'id' => $field->id,
            'label' => 'Nama baru',
            'sort_order' => 2,
        ]);
    }

    public function test_field_of_another_template_cannot_be_edited(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $field = DocumentTemplateField::factory()->create();

        $this->get(route('admin.templates.fields.edit', [$template, $field]))->assertNotFound();
        $this->delete(route('admin.templates.fields.destroy', [$template, $field]))->assertNotFound();

        $this->assertDatabaseHas('document_template_fields', ['id' => $field->id]);
    }

    public function test_deleting_field_keeps_its_section(): void
    {
        $this->actingAs($this->admin);

        $template = DocumentTemplate::factory()->create();
        $section = DocumentTemplateSection::factory()->create(['document_template_id' => $template->id]);
        $field = DocumentTemplateField::factory()->create([
            'document_template_section_id' => $section->id,
        ]);

        $this->delete(route('admin.templates.fields.destroy', [$template, $field]))
            ->assertRedirect(route('admin.templates.edit', $template));

        $this->assertDatabaseMissing('document_template_fields', ['id' => $field->id]);
        $this->assertDatabaseHas('document_template_sections', ['id' => $section->id]);
    }

    /*
     * ---------------------------------------------------------------------
     * Seeder dan penyusunan struktur
     * ---------------------------------------------------------------------
     */

    public function test_seeded_soap_template_has_four_sections_with_mapping_targets(): void
    {
        $this->seed(SoapTemplateSeeder::class);

        $template = DocumentTemplate::sole()->load('sections.fields');

        $this->assertSame(
            ['subjective', 'objective', 'assessment', 'plan'],
            $template->sections->pluck('key')->all(),
        );

        $this->assertSame(
            [
                'keluhan_utama',
                'riwayat_penyakit',
                'riwayat_alergi',
                'riwayat_obat',
                'tekanan_darah',
                'denyut_nadi',
                'suhu',
                'berat_badan',
                'pemeriksaan_fisik',
                'diagnosis',
                'diagnosis_banding',
                'terapi',
                'instruksi',
                'kontrol',
            ],
            $template->sections->flatMap(fn ($section) => $section->fields->pluck('key'))->all(),
        );

        $this->assertTrue($template->hasStructure());
    }

    public function test_soap_seeder_does_not_duplicate_data_when_run_twice(): void
    {
        $this->seed(SoapTemplateSeeder::class);
        $this->seed(SoapTemplateSeeder::class);

        $this->assertSame(1, DocumentType::count());
        $this->assertSame(1, DocumentTemplate::count());
        $this->assertSame(4, DocumentTemplateSection::count());
        $this->assertSame(14, DocumentTemplateField::count());
    }

    public function test_template_without_sections_is_reported_as_having_no_structure(): void
    {
        $template = DocumentTemplate::factory()->create();

        $this->assertFalse($template->hasStructure());
    }

    /*
     * ---------------------------------------------------------------------
     * Tampilan editor struktur
     * ---------------------------------------------------------------------
     */

    public function test_structure_editor_shows_sections_fields_and_hint(): void
    {
        $this->actingAs($this->admin);

        $this->seed(SoapTemplateSeeder::class);

        $template = DocumentTemplate::sole();

        $this->get(route('admin.templates.edit', $template))
            ->assertOk()
            ->assertSee('Subjective')
            ->assertSee('Keluhan utama')
            ->assertSee('Tekanan darah (mmHg)')
            ->assertSee('Wajib')
            ->assertSee('Keluhan dan riwayat yang diceritakan pasien');
    }

    public function test_field_form_preselects_the_section_it_was_opened_from(): void
    {
        $this->actingAs($this->admin);

        $this->seed(SoapTemplateSeeder::class);

        $template = DocumentTemplate::sole();
        $section = $template->sections()->where('key', 'objective')->sole();

        $this->get(route('admin.templates.fields.create', ['template' => $template, 'section' => $section->id]))
            ->assertOk()
            ->assertSee('Objective', false)
            ->assertSee('value="'.$section->id.'" selected', false);
    }

    public function test_staff_does_not_see_template_link_on_dashboard_and_navbar(): void
    {
        $this->actingAs($this->staff);

        $this->get(route('dashboard'))->assertOk()->assertDontSee('Template dokumen');

        $this->get(route('dashboard'))->assertDontSee(route('admin.document-types.index'), false);
    }

    public function test_admin_sees_document_type_count_on_dashboard(): void
    {
        $this->seed(SoapTemplateSeeder::class);

        $this->actingAs($this->admin);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Template dokumen')
            ->assertSee('1 jenis dokumen');
    }

    /*
     * ---------------------------------------------------------------------
     * Komponen isian
     * ---------------------------------------------------------------------
     */

    public function test_input_component_merges_caller_class_instead_of_repeating_class_attribute(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.document-types.create'));

        $html = $response->getContent();

        preg_match('/<input[^>]*id="code"[^>]*>/', $html, $matches);

        $this->assertNotEmpty($matches, 'Input kode tidak ditemukan pada halaman.');
        $this->assertSame(1, substr_count($matches[0], 'class='), 'Atribut class(Input terulang.');
        $this->assertStringContainsString('font-mono', $matches[0]);
        $this->assertStringContainsString('rounded-lg', $matches[0]);
    }
}
