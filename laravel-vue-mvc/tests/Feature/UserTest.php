<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use CreatesRolesAndUsers, RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        $role = $this->role('staff');

        return array_merge([
            'name' => 'User Baru',
            'email' => 'baru@example.com',
            'role_id' => $role->id,
            'password' => 'Password@123',
        ], $overrides);
    }

    /**
     * GET /api/users — admin can list users (200).
     */
    public function test_admin_can_list_users(): void
    {
        $this->actingAsRole('admin');
        User::factory()->count(3)->create(['role_id' => $this->role('staff')->id]);

        $response = $this->getJson('/api/users');

        $response->assertOk();
        $response->assertJsonCount(4);
        $response->assertJsonStructure(['0' => ['role']]);
    }

    /**
     * POST /api/users — admin can store a user (201).
     */
    public function test_admin_can_store_user(): void
    {
        $this->actingAsRole('admin');

        $response = $this->postJson('/api/users', $this->validData());

        $response->assertStatus(201);
        $response->assertJsonPath('email', 'baru@example.com');
        $response->assertJsonMissing(['password']);
        $this->assertDatabaseHas('users', ['email' => 'baru@example.com']);
    }

    /**
     * POST /api/users — duplicate email fails validation (422).
     */
    public function test_admin_cannot_store_duplicate_email(): void
    {
        $this->actingAsRole('admin');
        $existing = User::factory()->create(['email' => 'baru@example.com']);

        $response = $this->postJson('/api/users', $this->validData());

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertDatabaseCount('users', 2);
        $this->assertNotEquals($existing->id, $response->json('id'));
    }

    /**
     * POST /api/users — weak password fails validation (422).
     */
    public function test_admin_cannot_store_user_with_weak_password(): void
    {
        $this->actingAsRole('admin');

        $response = $this->postJson('/api/users', $this->validData([
            'password' => 'weak',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        $this->assertDatabaseCount('users', 1);
    }

    /**
     * GET /api/users/{id} — admin can show a user (200).
     */
    public function test_admin_can_show_user(): void
    {
        $this->actingAsRole('admin');
        $user = User::factory()->create(['role_id' => $this->role('staff')->id]);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertOk();
        $response->assertJsonPath('id', $user->id);
    }

    /**
     * GET /api/users/{id} — missing user returns 404.
     */
    public function test_show_missing_user_returns_404(): void
    {
        $this->actingAsRole('admin');

        $response = $this->getJson('/api/users/99999');

        $response->assertNotFound();
    }

    /**
     * PUT /api/users/{id} — admin can update a user (200).
     */
    public function test_admin_can_update_user(): void
    {
        $this->actingAsRole('admin');
        $user = User::factory()->create(['role_id' => $this->role('staff')->id]);
        $opdRole = $this->role('opd');

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Nama Diubah',
            'email' => $user->email,
            'role_id' => $opdRole->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Diubah',
            'role_id' => $opdRole->id,
        ]);
    }

    /**
     * PUT /api/users/{id} — admin can reset a user's password (200).
     */
    public function test_admin_can_reset_user_password(): void
    {
        $this->actingAsRole('admin');
        $user = User::factory()->create(['role_id' => $this->role('staff')->id, 'password' => Hash::make('Password@123')]);

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'password' => 'BaruPassword@123',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('BaruPassword@123', $user->fresh()->password));
    }

    /**
     * PUT /api/users/{id} — invalid payload fails validation (422).
     */
    public function test_admin_cannot_update_user_with_invalid_payload(): void
    {
        $this->actingAsRole('admin');
        $user = User::factory()->create(['role_id' => $this->role('staff')->id]);

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => '',
            'email' => 'not-an-email',
            'role_id' => 99999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'role_id']);
    }

    /**
     * DELETE /api/users/{id} — admin can delete another user (204).
     */
    public function test_admin_can_delete_user(): void
    {
        $this->actingAsRole('admin');
        $user = User::factory()->create(['role_id' => $this->role('staff')->id]);

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * DELETE /api/users/{id} — admin cannot delete their own account (422).
     */
    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->actingAsRole('admin');

        $response = $this->deleteJson("/api/users/{$admin->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    /**
     * GET /api/users — non-admin roles get 403.
     */
    public function test_staff_cannot_access_users(): void
    {
        $this->actingAsRole('staff');

        $this->getJson('/api/users')->assertStatus(403);
        $this->postJson('/api/users', $this->validData())->assertStatus(403);
        $this->deleteJson('/api/users/1')->assertStatus(403);
    }

    /**
     * GET /api/users — unauthenticated users get 401.
     */
    public function test_guest_cannot_access_users(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }
}
