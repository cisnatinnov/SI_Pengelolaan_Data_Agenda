<?php

namespace Tests\Feature;

use App\Models\Pengingat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengingatTest extends TestCase
{
    use CreatesRolesAndUsers, RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'judul' => 'Rapat Penting',
            'deskripsi' => 'Jangan lupa hadir.',
            'tanggal_pengingat' => '2026-08-25 08:00:00',
            'prioritas' => 'tinggi',
            'status' => 'pending',
        ], $overrides);
    }

    private function makePengingat(User $user): Pengingat
    {
        return Pengingat::factory()->create(['user_id' => $user->id]);
    }

    /**
     * GET /api/pengingat — non-admin roles see only their own reminders (200).
     */
    public function test_staff_sees_only_own_pengingat(): void
    {
        $user = $this->actingAsRole('staff');
        $this->makePengingat($user);
        $this->makePengingat($this->user('opd'));

        $response = $this->getJson('/api/pengingat');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.user_id', $user->id);
    }

    /**
     * POST /api/pengingat — non-admin roles can store a reminder (201).
     */
    public function test_staff_can_store_pengingat(): void
    {
        $user = $this->actingAsRole('staff');

        $response = $this->postJson('/api/pengingat', $this->validData());

        $response->assertStatus(201);
        $response->assertJsonPath('judul', 'Rapat Penting');
        $this->assertDatabaseHas('pengingats', [
            'user_id' => $user->id,
            'judul' => 'Rapat Penting',
        ]);
    }

    /**
     * POST /api/pengingat — invalid payload fails validation (422).
     */
    public function test_staff_cannot_store_invalid_pengingat(): void
    {
        $this->actingAsRole('staff');

        $response = $this->postJson('/api/pengingat', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'judul', 'tanggal_pengingat', 'prioritas', 'status',
        ]);
        $this->assertDatabaseCount('pengingats', 0);
    }

    /**
     * GET /api/pengingat/{id} — user can show own reminder (200).
     */
    public function test_staff_can_show_own_pengingat(): void
    {
        $user = $this->actingAsRole('staff');
        $pengingat = $this->makePengingat($user);

        $response = $this->getJson("/api/pengingat/{$pengingat->id}");

        $response->assertOk();
        $response->assertJsonPath('id', $pengingat->id);
    }

    /**
     * GET /api/pengingat/{id} — another user's reminder returns 404.
     */
    public function test_staff_cannot_show_others_pengingat(): void
    {
        $this->actingAsRole('staff');
        $other = $this->makePengingat($this->user('opd'));

        $response = $this->getJson("/api/pengingat/{$other->id}");

        $response->assertNotFound();
    }

    /**
     * PUT /api/pengingat/{id} — user can update own reminder (200).
     */
    public function test_staff_can_update_own_pengingat(): void
    {
        $user = $this->actingAsRole('staff');
        $pengingat = $this->makePengingat($user);

        $response = $this->putJson("/api/pengingat/{$pengingat->id}", $this->validData([
            'judul' => 'Judul Diubah',
            'status' => 'selesai',
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('pengingats', [
            'id' => $pengingat->id,
            'judul' => 'Judul Diubah',
            'status' => 'selesai',
        ]);
    }

    /**
     * PUT /api/pengingat/{id} — another user's reminder returns 404.
     */
    public function test_staff_cannot_update_others_pengingat(): void
    {
        $this->actingAsRole('staff');
        $other = $this->makePengingat($this->user('opd'));

        $response = $this->putJson("/api/pengingat/{$other->id}", $this->validData());

        $response->assertNotFound();
    }

    /**
     * PUT /api/pengingat/{id} — invalid payload fails validation (422).
     */
    public function test_staff_cannot_update_invalid_pengingat(): void
    {
        $user = $this->actingAsRole('staff');
        $pengingat = $this->makePengingat($user);

        $response = $this->putJson("/api/pengingat/{$pengingat->id}", [
            'prioritas' => 'biasa',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['prioritas']);
    }

    /**
     * DELETE /api/pengingat/{id} — user can delete own reminder (204).
     */
    public function test_staff_can_delete_own_pengingat(): void
    {
        $user = $this->actingAsRole('staff');
        $pengingat = $this->makePengingat($user);

        $response = $this->deleteJson("/api/pengingat/{$pengingat->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('pengingats', ['id' => $pengingat->id]);
    }

    /**
     * DELETE /api/pengingat/{id} — another user's reminder returns 404.
     */
    public function test_staff_cannot_delete_others_pengingat(): void
    {
        $this->actingAsRole('staff');
        $other = $this->makePengingat($this->user('opd'));

        $response = $this->deleteJson("/api/pengingat/{$other->id}");

        $response->assertNotFound();
    }

    /**
     * GET /api/pengingat — admin role gets 403.
     */
    public function test_admin_cannot_access_pengingat(): void
    {
        $this->actingAsRole('admin');
        $pengingat = $this->makePengingat($this->user('staff'));

        $this->getJson('/api/pengingat')->assertStatus(403);
        $this->postJson('/api/pengingat', $this->validData())->assertStatus(403);
        $this->deleteJson("/api/pengingat/{$pengingat->id}")->assertStatus(403);
        $this->assertDatabaseHas('pengingats', ['id' => $pengingat->id]);
    }

    /**
     * GET /api/pengingat — unauthenticated users get 401.
     */
    public function test_guest_cannot_access_pengingat(): void
    {
        $response = $this->getJson('/api/pengingat');

        $response->assertStatus(401);
    }
}
