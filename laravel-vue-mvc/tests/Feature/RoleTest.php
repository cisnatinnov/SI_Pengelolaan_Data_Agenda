<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use CreatesRolesAndUsers, RefreshDatabase;

    /**
     * GET /api/roles — admin can list roles (200).
     */
    public function test_admin_can_list_roles(): void
    {
        $this->actingAsRole('admin');
        Role::factory()->count(3)->create();

        $response = $this->getJson('/api/roles');

        $response->assertOk();
        $response->assertJsonCount(4);
    }

    /**
     * GET /api/roles — non-admin roles get 403.
     */
    public function test_non_admin_cannot_list_roles(): void
    {
        $this->actingAsRole('staff');

        $response = $this->getJson('/api/roles');

        $response->assertStatus(403);
    }

    /**
     * GET /api/roles — unauthenticated users get 401.
     */
    public function test_guest_cannot_list_roles(): void
    {
        $response = $this->getJson('/api/roles');

        $response->assertStatus(401);
    }
}
