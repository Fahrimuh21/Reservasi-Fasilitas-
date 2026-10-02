<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function register(string $email): int
    {
        return $this->postJson('/api/register', [
            'name' => 'Pendaftar Baru',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'mahasiswa',
        ])->assertCreated()
            ->assertJsonPath('user.status', 'pending')
            ->json('user.id');
    }

    public function test_admin_can_approve_pending_registration_and_user_can_then_login(): void
    {
        $id = $this->register('pendaftar@example.test');
        $this->postJson('/api/login', [
            'email' => 'pendaftar@example.test',
            'password' => 'password123',
        ])->assertUnprocessable()->assertJsonValidationErrors('account');

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);
        $this->getJson('/api/admin/accounts/pending')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $id);

        $this->postJson("/api/admin/accounts/{$id}/approve")
            ->assertOk()
            ->assertJsonPath('user.account_status', 'active');
        $this->assertDatabaseHas('users', [
            'id' => $id,
            'account_status' => 'active',
            'verified_by' => $admin->id,
        ]);
        $this->assertNotNull(User::findOrFail($id)->verified_at);
        $this->getJson('/api/admin/accounts/pending')->assertOk()->assertJsonCount(0, 'data');

        $this->postJson('/api/login', [
            'email' => 'pendaftar@example.test',
            'password' => 'password123',
        ])->assertOk()->assertJsonPath('user.account_status', 'active');
    }

    public function test_admin_can_reject_pending_registration_and_non_admin_is_forbidden(): void
    {
        $id = $this->register('ditolak@example.test');

        Sanctum::actingAs(User::factory()->create());
        $this->postJson("/api/admin/accounts/{$id}/approve")->assertForbidden();

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);
        $this->postJson("/api/admin/accounts/{$id}/reject")
            ->assertOk()
            ->assertJsonPath('user.account_status', 'rejected');
        $this->assertDatabaseHas('users', [
            'id' => $id,
            'account_status' => 'rejected',
            'verified_by' => $admin->id,
        ]);

        $this->postJson('/api/login', [
            'email' => 'ditolak@example.test',
            'password' => 'password123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('account')
            ->assertJsonPath('errors.account.0', 'Registrasi akun Anda telah ditolak. Hubungi Admin untuk informasi lebih lanjut.');
    }

    public function test_admin_can_approve_all_pending_accounts_at_once(): void
    {
        $pending = User::factory()->count(5)->pending()->create();
        $alreadyActive = User::factory()->create();
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/accounts/approve-all')
            ->assertOk()
            ->assertJsonPath('approved', 5);

        foreach ($pending as $user) {
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'account_status' => 'active',
                'verified_by' => $admin->id,
            ]);
        }
        $this->assertDatabaseHas('users', [
            'id' => $alreadyActive->id,
            'account_status' => 'active',
            'verified_by' => null,
        ]);
        $this->getJson('/api/admin/accounts/pending')->assertOk()->assertJsonCount(0, 'data');
        $this->postJson('/api/admin/accounts/approve-all')->assertOk()->assertJsonPath('approved', 0);
    }
}
