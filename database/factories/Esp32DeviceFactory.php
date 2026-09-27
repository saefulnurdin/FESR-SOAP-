<?php

namespace Database\Factories;

use App\Models\Esp32Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Esp32Device>
 */
class Esp32DeviceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Esp32Device>
     */
    protected $model = Esp32Device::class;

    public function definition(): array
    {
        return [
            'name' => 'Perekam '.fake()->unique()->numerify('#'),
            'user_id' => User::factory(),
            'token_hash' => hash('sha256', Str::random(40)),
            'last_seen_at' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function seenRecently(): static
    {
        return $this->state(fn (): array => [
            'last_seen_at' => now()->subMinutes(2),
        ]);
    }
}
