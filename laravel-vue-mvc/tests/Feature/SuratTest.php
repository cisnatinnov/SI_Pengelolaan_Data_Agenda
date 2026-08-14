<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\Pengingat;
use App\Models\Surat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratTest extends TestCase
{
    use CreatesRolesAndUsers, RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'tanggal' => '2026-08-15 09:00:00',
            'nomor_surat' => '005/UND/2026',
            'asal_surat' => 'Sekretariat Daerah',
            'perihal' => 'Undangan Rapat Koordinasi',
            'kepada' => 'Bapak/Ibu',
            'tanggal_pelaksanaan' => '2026-08-20 08:00:00',
            'tempat_pelaksanaan' => 'Ruang Rapat Utama',
            'pembawa_surat' => 'Joko',
            'tandatangan' => 'Sekretaris Daerah',
        ], $overrides);
    }

    /**
     * GET /api/surat — list all surat (200).
     */
    public function test_staff_can_list_surat(): void
    {
        $this->actingAsRole('staff');
        Surat::factory()->count(3)->create();

        $response = $this->getJson('/api/surat');

        $response->assertOk();
        $response->assertJsonCount(3);
    }

    /**
     * POST /api/surat — valid data creates a surat (201) plus disposisi and pengingat.
     */
    public function test_staff_can_store_surat(): void
    {
        $this->actingAsRole('staff');
        $asisten = $this->user('asisten_daerah');

        $response = $this->postJson('/api/surat', $this->validData());

        $response->assertStatus(201);
        $response->assertJsonPath('nomor_surat', '005/UND/2026');

        $this->assertDatabaseHas('surat', ['nomor_surat' => '005/UND/2026']);
        $this->assertDatabaseHas('disposisi', ['surat_id' => $response->json('id')]);
        $this->assertDatabaseHas('pengingats', [
            'user_id' => $asisten->id,
            'status' => 'pending',
        ]);
    }

    /**
     * POST /api/surat — missing required fields fails validation (422).
     */
    public function test_staff_cannot_store_invalid_surat(): void
    {
        $this->actingAsRole('staff');

        $response = $this->postJson('/api/surat', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'tanggal', 'nomor_surat', 'asal_surat', 'perihal',
            'kepada', 'tanggal_pelaksanaan', 'tempat_pelaksanaan', 'pembawa_surat',
        ]);
        $this->assertDatabaseCount('surat', 0);
    }

    /**
     * GET /api/surat/{id} — show a surat (200).
     */
    public function test_staff_can_show_surat(): void
    {
        $this->actingAsRole('staff');
        $surat = Surat::factory()->create();

        $response = $this->getJson("/api/surat/{$surat->id}");

        $response->assertOk();
        $response->assertJsonPath('id', $surat->id);
    }

    /**
     * GET /api/surat/{id} — missing surat returns 404.
     */
    public function test_show_missing_surat_returns_404(): void
    {
        $this->actingAsRole('staff');

        $response = $this->getJson('/api/surat/99999');

        $response->assertNotFound();
    }

    /**
     * PUT /api/surat/{id} — update a surat (200).
     */
    public function test_staff_can_update_surat(): void
    {
        $this->actingAsRole('staff');
        $surat = Surat::factory()->create();

        $response = $this->putJson("/api/surat/{$surat->id}", $this->validData([
            'perihal' => 'Perihal Diubah',
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('surat', [
            'id' => $surat->id,
            'perihal' => 'Perihal Diubah',
        ]);
    }

    /**
     * PUT /api/surat/{id} — invalid payload fails validation (422).
     */
    public function test_staff_cannot_update_invalid_surat(): void
    {
        $this->actingAsRole('staff');
        $surat = Surat::factory()->create();

        $response = $this->putJson("/api/surat/{$surat->id}", ['perihal' => '']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['perihal']);
    }

    /**
     * DELETE /api/surat/{id} — delete a surat (204).
     */
    public function test_staff_can_delete_surat(): void
    {
        $this->actingAsRole('staff');
        $surat = Surat::factory()->create();
        Disposisi::factory()->create(['surat_id' => $surat->id]);

        $response = $this->deleteJson("/api/surat/{$surat->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('surat', ['id' => $surat->id]);
    }

    /**
     * Any authenticated role (e.g. opd) may also read & create surat.
     */
    public function test_opd_can_list_and_store_surat(): void
    {
        $this->actingAsRole('opd');

        $this->getJson('/api/surat')->assertOk();

        $response = $this->postJson('/api/surat', $this->validData());

        $response->assertStatus(201);
    }

    /**
     * GET /api/surat — unauthenticated users get 401.
     */
    public function test_guest_cannot_access_surat(): void
    {
        $response = $this->getJson('/api/surat');

        $response->assertStatus(401);
    }

    /**
     * POST /api/surat — unauthenticated users get 401.
     */
    public function test_guest_cannot_store_surat(): void
    {
        $response = $this->postJson('/api/surat', $this->validData());

        $response->assertStatus(401);
    }
}
