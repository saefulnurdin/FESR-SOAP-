<?php

namespace Database\Factories;

use App\Enums\BloodType;
use App\Enums\Gender;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Nomor rekam medis sengaja tidak diisi karena diberikan model setelah
     * baris tersimpan.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());

        return [
            'nik' => fake()->unique()->numerify('##############'),
            'name' => fake()->name($gender === Gender::LakiLaki ? 'male' : 'female'),
            'gender' => $gender,
            'birth_date' => fake()->dateTimeBetween('-80 years', '-1 year'),
            'birth_place' => fake()->city(),
            'phone' => fake()->numerify('08##########'),
            'blood_type' => fake()->randomElement(BloodType::cases()),
            'address' => fake()->address(),
            'allergies' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the patient has no NIK, for example a newborn.
     */
    public function withoutNik(): static
    {
        return $this->state(fn (array $attributes): array => [
            'nik' => null,
        ]);
    }
}
