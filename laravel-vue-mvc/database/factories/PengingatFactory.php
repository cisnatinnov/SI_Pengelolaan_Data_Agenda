<?php

namespace Database\Factories;

use App\Models\Pengingat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengingat>
 */
class PengingatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'judul' => fake()->sentence(3),
            'deskripsi' => fake()->paragraph(),
            'tanggal_pengingat' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'prioritas' => 'sedang',
            'status' => 'pending',
            'source' => 'manual',
        ];
    }
}
