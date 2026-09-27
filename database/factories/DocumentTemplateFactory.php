<?php

namespace Database\Factories;

use App\Models\DocumentTemplate;
use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentTemplate>
 */
class DocumentTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<DocumentTemplate>
     */
    protected $model = DocumentTemplate::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'SOAP Dewasa',
            'SOAP Anak',
            'Asesmen Awal',
            'Triage IGD',
        ]);

        return [
            'document_type_id' => DocumentType::factory(),
            'name' => $name,
            'description' => fake()->sentence(8),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
