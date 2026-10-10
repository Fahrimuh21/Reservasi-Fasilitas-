<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_read_profile_and_logout(): void
    {
        $user = $this->createUser('user');

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('user.role', 'user')
            ->assertJsonStructure(['token', 'user']);

        $token = $login->json('token');

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('email', $user->email);

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_protected_api_requires_authentication_and_correct_role(): void
    {
        $this->getJson('/api/admin/users')->assertUnauthorized();

        Sanctum::actingAs($this->createUser('user'));

        $this->getJson('/api/admin/users')
            ->assertForbidden()
            ->assertJsonPath('your_role', 'user');
    }

    public function test_inactive_account_is_rejected_by_role_middleware(): void
    {
        $user = $this->createUser('officer', 'suspended');
        Sanctum::actingAs($user);

        $this->getJson('/api/officer/reservations')
            ->assertForbidden()
            ->assertJsonPath('message', 'Forbidden. Akun Anda tidak aktif.');
    }

    private function createUser(string $role, string $status = 'active'): User
    {
        return User::forceCreate([
            'name' => ucfirst($role).' Test',
            'email' => "{$role}-{$status}@example.test",
            'password' => Hash::make('password'),
            'role' => $role,
            'account_status' => $status,
            'registration_source' => 'admin_created',
        ]);
    }
}
