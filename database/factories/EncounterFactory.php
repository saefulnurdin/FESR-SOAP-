<?php

namespace Database\Factories;

use App\Enums\EncounterStatus;
use App\Enums\VisitType;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Encounter>
 */
class EncounterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => null,
            'occurred_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'visit_type' => fake()->randomElement(VisitType::cases()),
            'status' => EncounterStatus::Berjalan,
            'chief_complaint' => fake()->sentence(8),
            'notes' => null,
        ];
    }

    /**
     * Indicate that the encounter is handled by a user.
     */
    public function handledBy(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'doctor_id' => $user->getKey(),
        ]);
    }

    /**
     * Indicate that the encounter has been completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => EncounterStatus::Selesai,
        ]);
    }
}
