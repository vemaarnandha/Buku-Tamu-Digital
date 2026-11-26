<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['aktif' => false]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_dashboard_redirects_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('dashboard.kunjungan.index'));
    }

    public function test_role_middleware_blocks_unauthorized_role(): void
    {
        $pegawai = User::factory()->create(['role' => Role::Pegawai]);

        $response = $this->actingAs($pegawai)->get('/dashboard/users');

        $response->assertStatus(403);
    }

    public function test_role_middleware_allows_authorized_role(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $response = $this->actingAs($admin)->get('/dashboard/users');

        $response->assertStatus(200);
    }

    public function test_guest_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }
}
