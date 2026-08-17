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

    /**
     * GET /api/pengingat/notifications — only auto-generated (surat/kegiatan) pengingat are returned.
     */
    public function test_notifications_returns_only_auto_generated_pengingat(): void
    {
        $user = $this->actingAsRole('staff');

        Pengingat::create([
            'user_id' => $user->id,
            'judul' => 'Surat masuk diterima: 001',
            'tanggal_pengingat' => '2026-08-20 08:00:00',
            'prioritas' => 'sedang',
            'status' => 'pending',
            'source' => 'surat',
        ]);
        Pengingat::create([
            'user_id' => $user->id,
            'judul' => 'Kegiatan baru: Rapat',
            'tanggal_pengingat' => '2026-08-20 09:00:00',
            'prioritas' => 'sedang',
            'status' => 'pending',
            'source' => 'kegiatan',
        ]);
        $this->makePengingat($user);

        $response = $this->getJson('/api/pengingat/notifications');

        $response->assertOk();
        $response->assertJsonPath('unread_count', 2);
        $this->assertCount(2, $response->json('notifications'));
        foreach ($response->json('notifications') as $notification) {
            $this->assertNotEquals('manual', $notification['source']);
        }
    }

    /**
     * GET /api/pengingat/notifications — reads are not counted as unread.
     */
    public function test_notifications_tracks_read_state(): void
    {
        $user = $this->actingAsRole('staff');

        Pengingat::create([
            'user_id' => $user->id,
            'judul' => 'Surat masuk diterima: 001',
            'tanggal_pengingat' => '2026-08-20 08:00:00',
            'prioritas' => 'sedang',
            'status' => 'pending',
            'source' => 'surat',
            'read_at' => now(),
        ]);
        Pengingat::create([
            'user_id' => $user->id,
            'judul' => 'Kegiatan baru: Rapat',
            'tanggal_pengingat' => '2026-08-20 09:00:00',
            'prioritas' => 'sedang',
            'status' => 'pending',
            'source' => 'kegiatan',
        ]);

        $response = $this->getJson('/api/pengingat/notifications');

        $response->assertOk();
        $response->assertJsonPath('unread_count', 1);
        $this->assertCount(2, $response->json('notifications'));
    }

    /**
     * POST /api/pengingat/{id}/read — marks the user's notification as read (200).
     */
    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = $this->actingAsRole('staff');
        $pengingat = Pengingat::create([
            'user_id' => $user->id,
            'judul' => 'Surat masuk diterima: 001',
            'tanggal_pengingat' => '2026-08-20 08:00:00',
            'prioritas' => 'sedang',
            'status' => 'pending',
            'source' => 'surat',
        ]);

        $response = $this->postJson("/api/pengingat/{$pengingat->id}/read");

        $response->assertOk();
        $this->assertNotNull($pengingat->fresh()->read_at);
    }

    /**
     * POST /api/pengingat/{id}/read — another user's notification returns 404.
     */
    public function test_user_cannot_mark_others_notification_as_read(): void
    {
        $this->actingAsRole('staff');
        $other = Pengingat::create([
            'user_id' => $this->user('opd')->id,
            'judul' => 'Surat masuk diterima: 001',
            'tanggal_pengingat' => '2026-08-20 08:00:00',
            'prioritas' => 'sedang',
            'status' => 'pending',
            'source' => 'surat',
        ]);

        $response = $this->postJson("/api/pengingat/{$other->id}/read");

        $response->assertNotFound();
        $this->assertNull($other->fresh()->read_at);
    }

    /**
     * POST /api/pengingat/read-all — marks every notification as read (200).
     */
    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = $this->actingAsRole('staff');

        foreach (['surat', 'kegiatan'] as $source) {
            Pengingat::create([
                'user_id' => $user->id,
                'judul' => "Auto $source",
                'tanggal_pengingat' => '2026-08-20 08:00:00',
                'prioritas' => 'sedang',
                'status' => 'pending',
                'source' => $source,
            ]);
        }

        $response = $this->postJson('/api/pengingat/read-all');

        $response->assertOk();
        $this->assertDatabaseMissing('pengingats', ['user_id' => $user->id, 'read_at' => null]);
    }

    /**
     * GET /api/pengingat/notifications — admin role gets 403.
     */
    public function test_admin_cannot_access_notifications(): void
    {
        $this->actingAsRole('admin');

        $this->getJson('/api/pengingat/notifications')->assertStatus(403);
        $this->postJson('/api/pengingat/read-all')->assertStatus(403);
    }

    /**
     * GET /api/pengingat/notifications — unauthenticated users get 401.
     */
    public function test_guest_cannot_access_notifications(): void
    {
        $this->getJson('/api/pengingat/notifications')->assertStatus(401);
    }
}
