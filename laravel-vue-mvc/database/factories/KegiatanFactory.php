<?php

namespace Database\Factories;

use App\Models\Kegiatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kegiatan>
 */
class KegiatanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kegiatan' => fake()->sentence(3),
            'tempat_kegiatan' => fake()->city(),
            'tanggal_kegiatan' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'uraian_kegiatan' => fake()->paragraph(),
            'realisasi_pelaksanaan' => 'terlaksana',
            'keterangan' => null,
            'status' => 'pelaksanaan',
            'nama_penyusun' => fake()->name(),
        ];
    }
}
