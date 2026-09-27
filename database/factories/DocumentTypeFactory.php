<?php

namespace Database\Factories;

use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DocumentType>
 */
class DocumentTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<DocumentType>
     */
    protected $model = DocumentType::class;

    /**
     * Kode jenis dokumen memakai huruf kecil tanpa spasi agar pemanggilannya
     * ringkas, misalnya soap atau asesmen_awal.
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Catatan SOAP',
            'Asesmen Awal',
            'RME Rawat Jalan',
            'Formulir Triage',
        ]);

        return [
            'code' => Str::of($name)->slug('_')->value(),
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
