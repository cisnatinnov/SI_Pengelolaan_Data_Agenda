<?php

namespace Tests\Feature;

use App\Models\Kegiatan;
use App\Models\KegiatanKehadiran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanTest extends TestCase
{
    use CreatesRolesAndUsers, RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'nama_kegiatan' => 'Rapat Koordinasi Tahunan',
            'tempat_kegiatan' => 'Aula Kantor Bupati',
            'tanggal_kegiatan' => '2026-08-20 09:00:00',
            'uraian_kegiatan' => 'Pembahasan program kerja tahun berjalan.',
            'realisasi_pelaksanaan' => 'terlaksana',
            'keterangan' => null,
            'status' => 'pelaksanaan',
            'nama_penyusun' => 'Budi',
        ], $overrides);
    }

    /**
     * GET /api/kegiatan — any authenticated role can list (200).
     */
    public function test_all_roles_can_list_kegiatan(): void
    {
        $this->actingAsRole('opd');
        Kegiatan::factory()->count(3)->create();

        $response = $this->getJson('/api/kegiatan');

        $response->assertOk();
        $response->assertJsonCount(3);
    }

    /**
     * GET /api/kegiatan — listing includes hadir/tidak counts.
     */
    public function test_kegiatan_list_has_attendance_counts(): void
    {
        $this->actingAsRole('opd');
        $kegiatan = Kegiatan::factory()->create();
        KegiatanKehadiran::create(['kegiatan_id' => $kegiatan->id, 'user_id' => $this->user('opd')->id, 'status' => 'hadir']);

        $response = $this->getJson('/api/kegiatan');

        $response->assertOk();
        $response->assertJsonPath('0.hadir_count', 1);
        $response->assertJsonPath('0.tidak_count', 0);
    }

    /**
     * GET /api/kegiatan/{id} — any authenticated role can show (200).
     */
    public function test_all_roles_can_show_kegiatan(): void
    {
        $this->actingAsRole('staff');
        $kegiatan = Kegiatan::factory()->create();

        $response = $this->getJson("/api/kegiatan/{$kegiatan->id}");

        $response->assertOk();
        $response->assertJsonPath('id', $kegiatan->id);
    }

    /**
     * GET /api/kegiatan/{id} — missing kegiatan returns 404.
     */
    public function test_show_missing_kegiatan_returns_404(): void
    {
        $this->actingAsRole('staff');

        $response = $this->getJson('/api/kegiatan/99999');

        $response->assertNotFound();
    }

    /**
     * POST /api/kegiatan — staff can store (201) and all users get a pengingat.
     */
    public function test_staff_can_store_kegiatan(): void
    {
        $this->actingAsRole('staff');
        $opd = $this->user('opd');

        $response = $this->postJson('/api/kegiatan', $this->validData());

        $response->assertStatus(201);
        $response->assertJsonPath('nama_kegiatan', 'Rapat Koordinasi Tahunan');
        $this->assertDatabaseHas('kegiatans', ['nama_kegiatan' => 'Rapat Koordinasi Tahunan']);
        $this->assertDatabaseHas('pengingats', ['user_id' => $opd->id, 'status' => 'pending']);
    }

    /**
     * POST /api/kegiatan — non-staff roles get 403.
     */
    public function test_opd_cannot_store_kegiatan(): void
    {
        $this->actingAsRole('opd');

        $response = $this->postJson('/api/kegiatan', $this->validData());

        $response->assertStatus(403);
        $this->assertDatabaseCount('kegiatans', 0);
    }

    /**
     * POST /api/kegiatan — invalid payload fails validation (422).
     */
    public function test_staff_cannot_store_invalid_kegiatan(): void
    {
        $this->actingAsRole('staff');

        $response = $this->postJson('/api/kegiatan', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'nama_kegiatan', 'tempat_kegiatan', 'tanggal_kegiatan',
            'uraian_kegiatan', 'realisasi_pelaksanaan', 'status',
        ]);
        $this->assertDatabaseCount('kegiatans', 0);
    }

    /**
     * POST /api/kegiatan — schedule conflict is rejected with 422.
     */
    public function test_staff_cannot_store_conflicting_kegiatan(): void
    {
        $this->actingAsRole('staff');
        Kegiatan::factory()->create(['tanggal_kegiatan' => '2026-08-20 09:00:00']);

        $response = $this->postJson('/api/kegiatan', $this->validData());

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tanggal_kegiatan']);
        $this->assertDatabaseCount('kegiatans', 1);
    }

    /**
     * PUT /api/kegiatan/{id} — staff can update (200).
     */
    public function test_staff_can_update_kegiatan(): void
    {
        $this->actingAsRole('staff');
        $kegiatan = Kegiatan::factory()->create(['tanggal_kegiatan' => '2026-08-20 09:00:00']);

        $response = $this->putJson("/api/kegiatan/{$kegiatan->id}", $this->validData([
            'nama_kegiatan' => 'Nama Diubah',
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('kegiatans', [
            'id' => $kegiatan->id,
            'nama_kegiatan' => 'Nama Diubah',
        ]);
    }

    /**
     * PUT /api/kegiatan/{id} — updating to a conflicting time is rejected (422).
     */
    public function test_staff_cannot_update_to_conflicting_kegiatan(): void
    {
        $this->actingAsRole('staff');
        $kegiatan = Kegiatan::factory()->create(['tanggal_kegiatan' => '2026-08-20 09:00:00']);
        Kegiatan::factory()->create(['tanggal_kegiatan' => '2026-08-21 10:00:00']);

        $response = $this->putJson("/api/kegiatan/{$kegiatan->id}", $this->validData([
            'tanggal_kegiatan' => '2026-08-21 10:00:00',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tanggal_kegiatan']);
    }

    /**
     * PUT /api/kegiatan/{id} — keeping the same schedule on update is allowed.
     */
    public function test_staff_can_update_without_conflict_when_keeping_same_time(): void
    {
        $this->actingAsRole('staff');
        $kegiatan = Kegiatan::factory()->create(['tanggal_kegiatan' => '2026-08-20 09:00:00']);

        $response = $this->putJson("/api/kegiatan/{$kegiatan->id}", $this->validData([
            'nama_kegiatan' => 'Tetap Sama Jam',
        ]));

        $response->assertOk();
    }

    /**
     * PUT /api/kegiatan/{id} — non-staff roles get 403.
     */
    public function test_opd_cannot_update_kegiatan(): void
    {
        $this->actingAsRole('opd');
        $kegiatan = Kegiatan::factory()->create();

        $response = $this->putJson("/api/kegiatan/{$kegiatan->id}", $this->validData());

        $response->assertStatus(403);
    }

    /**
     * DELETE /api/kegiatan/{id} — staff can delete (204).
     */
    public function test_staff_can_delete_kegiatan(): void
    {
        $this->actingAsRole('staff');
        $kegiatan = Kegiatan::factory()->create();

        $response = $this->deleteJson("/api/kegiatan/{$kegiatan->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('kegiatans', ['id' => $kegiatan->id]);
    }

    /**
     * DELETE /api/kegiatan/{id} — non-staff roles get 403.
     */
    public function test_opd_cannot_delete_kegiatan(): void
    {
        $this->actingAsRole('opd');
        $kegiatan = Kegiatan::factory()->create();

        $response = $this->deleteJson("/api/kegiatan/{$kegiatan->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('kegiatans', ['id' => $kegiatan->id]);
    }

    /**
     * POST /api/kegiatan/{id}/kehadiran — opd can confirm attendance (200).
     */
    public function test_opd_can_confirm_kehadiran(): void
    {
        $opd = $this->actingAsRole('opd');
        $kegiatan = Kegiatan::factory()->create();

        $response = $this->postJson("/api/kegiatan/{$kegiatan->id}/kehadiran", [
            'status' => 'hadir',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('kegiatan_kehadirans', [
            'kegiatan_id' => $kegiatan->id,
            'user_id' => $opd->id,
            'status' => 'hadir',
        ]);
    }

    /**
     * POST /api/kegiatan/{id}/kehadiran — re-confirming updates the record (200).
     */
    public function test_opd_can_change_kehadiran_status(): void
    {
        $opd = $this->actingAsRole('opd');
        $kegiatan = Kegiatan::factory()->create();

        $this->postJson("/api/kegiatan/{$kegiatan->id}/kehadiran", ['status' => 'hadir']);
        $response = $this->postJson("/api/kegiatan/{$kegiatan->id}/kehadiran", ['status' => 'tidak']);

        $response->assertOk();
        $this->assertDatabaseCount('kegiatan_kehadirans', 1);
        $this->assertDatabaseHas('kegiatan_kehadirans', [
            'kegiatan_id' => $kegiatan->id,
            'user_id' => $opd->id,
            'status' => 'tidak',
        ]);
    }

    /**
     * POST /api/kegiatan/{id}/kehadiran — invalid status fails validation (422).
     */
    public function test_opd_cannot_confirm_invalid_kehadiran_status(): void
    {
        $this->actingAsRole('opd');
        $kegiatan = Kegiatan::factory()->create();

        $response = $this->postJson("/api/kegiatan/{$kegiatan->id}/kehadiran", [
            'status' => 'mungkin',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    /**
     * POST /api/kegiatan/{id}/kehadiran — non-OPD roles get 403.
     */
    public function test_staff_cannot_confirm_kehadiran(): void
    {
        $this->actingAsRole('staff');
        $kegiatan = Kegiatan::factory()->create();

        $response = $this->postJson("/api/kegiatan/{$kegiatan->id}/kehadiran", [
            'status' => 'hadir',
        ]);

        $response->assertStatus(403);
    }

    /**
     * POST /api/kegiatan/{id}/kehadiran — missing kegiatan returns 404.
     */
    public function test_confirm_kehadiran_missing_kegiatan_returns_404(): void
    {
        $this->actingAsRole('opd');

        $response = $this->postJson('/api/kegiatan/99999/kehadiran', [
            'status' => 'hadir',
        ]);

        $response->assertNotFound();
    }

    /**
     * GET /api/kegiatan — unauthenticated users get 401.
     */
    public function test_guest_cannot_access_kegiatan(): void
    {
        $response = $this->getJson('/api/kegiatan');

        $response->assertStatus(401);
    }
}
