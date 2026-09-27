<?php

namespace Database\Factories;

use App\Enums\DocumentFieldType;
use App\Models\DocumentTemplateField;
use App\Models\DocumentTemplateSection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DocumentTemplateField>
 */
class DocumentTemplateFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<DocumentTemplateField>
     */
    protected $model = DocumentTemplateField::class;

    public function definition(): array
    {
        $label = fake()->unique()->randomElement([
            'Keluhan utama',
            'Tekanan darah',
            'Suhu tubuh',
            'Diagnosis',
            'Terapi',
        ]);

        return [
            'document_template_section_id' => DocumentTemplateSection::factory(),
            'key' => Str::slug($label, '_'),
            'label' => $label,
            'type' => fake()->randomElement(DocumentFieldType::cases()),
            'unit' => null,
            'is_required' => false,
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }

    /**
     * Field yang wajib diisi.
     */
    public function required(): static
    {
        return $this->state(fn (): array => ['is_required' => true]);
    }

    /**
     * Field pilihan dengan daftar opsi yang siap pakai.
     *
     * @param  list<string>  $options
     */
    public function select(array $options = ['Ya', 'Tidak']): static
    {
        return $this->state(fn (): array => [
            'type' => DocumentFieldType::Select,
            'options' => $options,
        ]);
    }
}
