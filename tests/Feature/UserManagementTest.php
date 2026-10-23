<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_a_manager_and_staff_account_that_can_sign_in(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->post('/users', [
            'name' => 'Amina Bello',
            'email' => 'amina@farm.test',
            'role' => 'manager',
            'password' => 'Strong-pass-1',
            'password_confirmation' => 'Strong-pass-1',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => 'amina@farm.test', 'role' => 'manager', 'is_active' => true]);

        auth()->logout();
        $this->post('/login', ['email' => 'amina@farm.test', 'password' => 'Strong-pass-1'])->assertRedirect();
        $this->assertAuthenticated();

        $this->actingAs(User::factory()->admin()->create());

        $this->post('/users', [
            'name' => 'Jude Staff',
            'email' => 'jude@farm.test',
            'role' => 'staff',
            'password' => 'Strong-pass-2',
            'password_confirmation' => 'Strong-pass-2',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => 'jude@farm.test', 'role' => 'staff', 'is_active' => true]);
    }

    public function test_manager_cannot_create_users(): void
    {
        $this->actingAs(User::factory()->manager()->create());

        $this->post('/users', [
            'name' => 'Blocked User',
            'email' => 'blocked@farm.test',
            'role' => 'staff',
            'password' => 'Strong-pass-3',
            'password_confirmation' => 'Strong-pass-3',
        ])->assertStatus(403);

        $this->assertDatabaseMissing('users', ['email' => 'blocked@farm.test']);
    }

    public function test_deactivating_a_user_blocks_their_sign_in(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->create(['email' => 'staff@farm.test']);

        $this->actingAs($admin)->patch("/users/{$staff->id}/active")->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'is_active' => false]);

        auth()->logout();
        $this->post('/login', ['email' => 'staff@farm.test', 'password' => 'password-123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->patch("/users/{$admin->id}/active")->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => true]);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'staff',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_one_admin_can_demote_another_while_another_active_admin_remains(): void
    {
        $first = User::factory()->admin()->create();
        $second = User::factory()->admin()->create();

        $this->actingAs($first)->put("/users/{$second->id}", [
            'name' => $second->name,
            'email' => $second->email,
            'role' => 'staff',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $second->id, 'role' => 'staff']);
    }

    public function test_staff_changes_own_password_only_with_current_password(): void
    {
        $staff = User::factory()->create(['email' => 'staff@farm.test']);
        $this->actingAs($staff);

        $this->put('/profile/password', [
            'current_password' => 'wrong-current',
            'password' => 'New-pass-456',
            'password_confirmation' => 'New-pass-456',
        ])->assertSessionHasErrors('current_password');

        $this->put('/profile/password', [
            'current_password' => 'password-123',
            'password' => 'New-pass-456',
            'password_confirmation' => 'New-pass-456',
        ])->assertSessionHasNoErrors();

        auth()->logout();
        $this->post('/login', ['email' => 'staff@farm.test', 'password' => 'New-pass-456'])->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_admin_can_reset_another_users_password(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->create(['email' => 'staff@farm.test']);

        $this->actingAs($admin)->put("/users/{$staff->id}/password", [
            'password' => 'Reset-pass-789',
            'password_confirmation' => 'Reset-pass-789',
        ])->assertSessionHasNoErrors();

        auth()->logout();
        $this->post('/login', ['email' => 'staff@farm.test', 'password' => 'Reset-pass-789'])->assertRedirect();
        $this->assertAuthenticated();
    }
}
