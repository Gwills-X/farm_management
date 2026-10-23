<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_sign_in(): void
    {
        $user = User::factory()->create(['email' => 'staff@farm.test']);

        $this->post('/login', ['email' => 'staff@farm.test', 'password' => 'password-123'])
            ->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_sign_in(): void
    {
        User::factory()->inactive()->create(['email' => 'gone@farm.test']);

        $this->post('/login', ['email' => 'gone@farm.test', 'password' => 'password-123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_wrong_password_is_refused(): void
    {
        User::factory()->create(['email' => 'staff@farm.test']);

        $this->post('/login', ['email' => 'staff@farm.test', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/animals')->assertRedirect('/login');
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }
}
