<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\Surat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisposisiTest extends TestCase
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
            'pembawa_surat' => 'Joko',
            'tandatangan_penerima' => null,
            'tandatangan_dituju' => null,
            'keterangan' => 'diterima',
            'alasan' => null,
        ], $overrides);
    }

    private function makeDisposisi(): Disposisi
    {
        return Disposisi::factory()->create();
    }

    /**
     * GET /api/disposisi — list all disposisi (200).
     */
    public function test_staff_can_list_disposisi(): void
    {
        $this->actingAsRole('staff');
        Disposisi::factory()->count(3)->create();

        $response = $this->getJson('/api/disposisi');

        $response->assertOk();
        $response->assertJsonCount(3);
    }

    /**
     * GET /api/disposisi?surat_id= — filter by surat (200).
     */
    public function test_staff_can_filter_disposisi_by_surat(): void
    {
        $this->actingAsRole('staff');
        $target = Surat::factory()->create();
        Disposisi::factory()->create(['surat_id' => $target->id]);
        Disposisi::factory()->create();

        $response = $this->getJson("/api/disposisi?surat_id={$target->id}");

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.surat_id', $target->id);
    }

    /**
     * GET /api/disposisi/{id} — show a disposisi (200).
     */
    public function test_staff_can_show_disposisi(): void
    {
        $this->actingAsRole('staff');
        $disposisi = $this->makeDisposisi();

        $response = $this->getJson("/api/disposisi/{$disposisi->id}");

        $response->assertOk();
        $response->assertJsonPath('id', $disposisi->id);
        $response->assertJsonStructure(['surat']);
    }

    /**
     * GET /api/disposisi/{id} — missing disposisi returns 404.
     */
    public function test_show_missing_disposisi_returns_404(): void
    {
        $this->actingAsRole('staff');

        $response = $this->getJson('/api/disposisi/99999');

        $response->assertNotFound();
    }

    /**
     * PUT /api/disposisi/{id} — staff can update a disposisi (200).
     */
    public function test_staff_can_update_disposisi(): void
    {
        $this->actingAsRole('staff');
        $disposisi = $this->makeDisposisi();

        $response = $this->putJson("/api/disposisi/{$disposisi->id}", $this->validData([
            'perihal' => 'Perihal Diubah',
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('disposisi', [
            'id' => $disposisi->id,
            'perihal' => 'Perihal Diubah',
        ]);
    }

    /**
     * PUT /api/disposisi/{id} — alasan is cleared unless rejected.
     */
    public function test_staff_update_clears_alasan_for_non_rejected(): void
    {
        $this->actingAsRole('staff');
        $disposisi = Disposisi::factory()->create(['keterangan' => 'ditolak', 'alasan' => 'Data tidak lengkap']);

        $response = $this->putJson("/api/disposisi/{$disposisi->id}", $this->validData([
            'keterangan' => 'diterima',
            'alasan' => 'Harusnya kosong',
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('disposisi', [
            'id' => $disposisi->id,
            'keterangan' => 'diterima',
            'alasan' => null,
        ]);
    }

    /**
     * PUT /api/disposisi/{id} — invalid payload fails validation (422).
     */
    public function test_staff_cannot_update_invalid_disposisi(): void
    {
        $this->actingAsRole('staff');
        $disposisi = $this->makeDisposisi();

        $response = $this->putJson("/api/disposisi/{$disposisi->id}", ['perihal' => '']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['perihal']);
    }

    /**
     * PUT /api/disposisi/{id} — asisten_daerah can sahkan (diserahkan) (200).
     */
    public function test_asisten_daerah_can_approve_disposisi(): void
    {
        $this->actingAsRole('asisten_daerah');
        $disposisi = $this->makeDisposisi();

        $response = $this->putJson("/api/disposisi/{$disposisi->id}", [
            'keterangan' => 'diserahkan',
            'alasan' => null,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('disposisi', [
            'id' => $disposisi->id,
            'keterangan' => 'diserahkan',
            'alasan' => null,
        ]);
    }

    /**
     * PUT /api/disposisi/{id} — asisten_daerah can reject (ditolak) with alasan (200).
     */
    public function test_asisten_daerah_can_reject_disposisi(): void
    {
        $this->actingAsRole('asisten_daerah');
        $disposisi = $this->makeDisposisi();

        $response = $this->putJson("/api/disposisi/{$disposisi->id}", [
            'keterangan' => 'ditolak',
            'alasan' => 'Jadwal bentrok',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('disposisi', [
            'id' => $disposisi->id,
            'keterangan' => 'ditolak',
            'alasan' => 'Jadwal bentrok',
        ]);
    }

    /**
     * PUT /api/disposisi/{id} — asisten_daerah must provide alasan when rejecting (422).
     */
    public function test_asisten_daerah_must_provide_alasan_when_rejecting(): void
    {
        $this->actingAsRole('asisten_daerah');
        $disposisi = $this->makeDisposisi();

        $response = $this->putJson("/api/disposisi/{$disposisi->id}", [
            'keterangan' => 'ditolak',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['alasan']);
    }

    /**
     * PUT /api/disposisi/{id} — asisten_daerah cannot use staff-only fields (422).
     */
    public function test_asisten_daerah_cannot_use_staff_fields(): void
    {
        $this->actingAsRole('asisten_daerah');
        $disposisi = $this->makeDisposisi();

        $response = $this->putJson("/api/disposisi/{$disposisi->id}", [
            'keterangan' => 'diterima',
            'alasan' => 'Tidak diizinkan',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['keterangan']);
    }

    /**
     * PUT /api/disposisi/{id} — other roles (opd) get 403.
     */
    public function test_opd_cannot_update_disposisi(): void
    {
        $this->actingAsRole('opd');
        $disposisi = $this->makeDisposisi();

        $response = $this->putJson("/api/disposisi/{$disposisi->id}", [
            'keterangan' => 'diserahkan',
        ]);

        $response->assertStatus(403);
    }

    /**
     * DELETE /api/disposisi/{id} — staff can delete (204).
     */
    public function test_staff_can_delete_disposisi(): void
    {
        $this->actingAsRole('staff');
        $disposisi = $this->makeDisposisi();

        $response = $this->deleteJson("/api/disposisi/{$disposisi->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('disposisi', ['id' => $disposisi->id]);
    }

    /**
     * DELETE /api/disposisi/{id} — non-staff roles get 403.
     */
    public function test_asisten_daerah_cannot_delete_disposisi(): void
    {
        $this->actingAsRole('asisten_daerah');
        $disposisi = $this->makeDisposisi();

        $response = $this->deleteJson("/api/disposisi/{$disposisi->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('disposisi', ['id' => $disposisi->id]);
    }

    /**
     * GET /api/disposisi — unauthenticated users get 401.
     */
    public function test_guest_cannot_access_disposisi(): void
    {
        $response = $this->getJson('/api/disposisi');

        $response->assertStatus(401);
    }
}
