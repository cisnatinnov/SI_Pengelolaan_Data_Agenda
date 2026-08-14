<?php

namespace Database\Factories;

use App\Models\Disposisi;
use App\Models\Surat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Disposisi>
 */
class DisposisiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'surat_id' => Surat::factory(),
            'tanggal' => fake()->dateTime(),
            'nomor_surat' => '005/UND/'.fake()->unique()->numberBetween(100, 999).'/2026',
            'asal_surat' => fake()->company(),
            'perihal' => fake()->sentence(3),
            'kepada' => 'Bapak/Ibu',
            'pembawa_surat' => fake()->name(),
            'tandatangan_penerima' => null,
            'tandatangan_dituju' => null,
            'keterangan' => 'diterima',
            'alasan' => null,
        ];
    }
}
