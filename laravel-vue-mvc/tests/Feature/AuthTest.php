<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use CreatesRolesAndUsers, RefreshDatabase;

    /**
     * GET /login — render the login form.
     */
    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * GET /login — authenticated users are redirected to "/".
     */
    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = $this->user('staff');
        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }

    /**
     * POST /login — valid credentials authenticate and redirect (302).
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password@123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password@123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * POST /login — wrong credentials are rejected with a validation error.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'staff@example.com', 'password' => Hash::make('Password@123')]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'staff@example.com',
            'password' => 'WrongPassword1!',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * POST /login — malformed payload fails validation (422-style session errors).
     */
    public function test_login_validates_required_fields_and_password_strength(): void
    {
        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => 'weak',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * POST /login — remember me checkbox is honoured.
     */
    public function test_login_remember_me(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password@123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password@123',
            'remember' => 'on',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);

        $cookies = collect($response->headers->getCookies())->map(fn ($cookie) => $cookie->getName());
        $this->assertTrue($cookies->contains(fn ($name) => str_starts_with($name, 'remember_web_')), 'Remember-me cookie missing.');
    }

    /**
     * POST /logout — authenticated users are logged out (302 redirect).
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->user('staff');

        $response = $this->actingAs($user)->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * POST /logout — unauthenticated users are redirected to login.
     */
    public function test_guest_cannot_logout(): void
    {
        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * GET / — guests are redirected to login.
     */
    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * GET / — authenticated users see the dashboard.
     */
    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = $this->user('staff');

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
