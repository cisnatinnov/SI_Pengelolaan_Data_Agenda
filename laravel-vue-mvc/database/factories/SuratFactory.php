<?php

namespace Database\Factories;

use App\Models\Surat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Surat>
 */
class SuratFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tanggal' => fake()->dateTime(),
            'nomor_surat' => '005/UND/'.fake()->unique()->numberBetween(100, 999).'/2026',
            'asal_surat' => fake()->company(),
            'perihal' => fake()->sentence(3),
            'kepada' => 'Bapak/Ibu',
            'tanggal_pelaksanaan' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'tempat_pelaksanaan' => fake()->city(),
            'pembawa_surat' => fake()->name(),
            'tandatangan' => fake()->name(),
        ];
    }
}
