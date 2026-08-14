<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;

trait CreatesRolesAndUsers
{
    /** @var array<string, Role> */
    protected array $roles = [];

    /**
     * Get (and cache) a role by slug.
     */
    protected function role(string $slug): Role
    {
        return $this->roles[$slug] ??= Role::create([
            'name' => ucwords(str_replace('_', ' ', $slug)),
            'slug' => $slug,
        ]);
    }

    /**
     * Create a user with the given role slug.
     */
    protected function user(string $roleSlug): User
    {
        return User::factory()->create([
            'role_id' => $this->role($roleSlug)->id,
        ]);
    }

    /**
     * Create a user with the given role and act as them.
     */
    protected function actingAsRole(string $roleSlug): User
    {
        $user = $this->user($roleSlug);

        $this->actingAs($user);

        return $user;
    }
}
