<?php

namespace Database\Factories;

use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateSection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DocumentTemplateSection>
 */
class DocumentTemplateSectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<DocumentTemplateSection>
     */
    protected $model = DocumentTemplateSection::class;

    public function definition(): array
    {
        $title = fake()->unique()->randomElement([
            'Subjective',
            'Objective',
            'Assessment',
            'Plan',
            'Tanda Vital',
        ]);

        return [
            'document_template_id' => DocumentTemplate::factory(),
            'key' => Str::slug($title, '_'),
            'title' => $title,
            'hint' => fake()->sentence(10),
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}
