<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\AnimalType;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_create_animal_types(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/animal-types', ['name' => 'Rabbits', 'code_prefix' => 'RAB', 'is_active' => true])
            ->assertForbidden();

        $this->assertDatabaseMissing('animal_types', ['name' => 'Rabbits']);
    }

    public function test_staff_cannot_delete_animals(): void
    {
        $this->actingAs(User::factory()->create());
        $animal = Animal::factory()->create();

        $this->delete("/animals/{$animal->id}")->assertForbidden();

        $this->assertDatabaseHas('animals', ['id' => $animal->id]);
    }

    public function test_staff_cannot_delete_expenses(): void
    {
        $this->actingAs(User::factory()->create());
        $expense = Expense::factory()->create();

        $this->delete("/expenses/{$expense->id}")->assertForbidden();

        $this->assertDatabaseHas('expenses', ['id' => $expense->id]);
    }

    public function test_staff_cannot_open_user_management(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/users')->assertForbidden();
    }

    public function test_admin_can_open_user_management(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/users')->assertOk();
    }

    public function test_staff_can_register_animals(): void
    {
        $this->actingAs(User::factory()->create());
        $type = AnimalType::factory()->create();

        $this->post('/animals', [
            'animal_type_id' => $type->id,
            'sex' => 'female',
            'acquisition_type' => 'born_on_farm',
            'acquisition_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertDatabaseCount('animals', 1);
    }

    public function test_admin_can_delete_an_empty_animal(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $animal = Animal::factory()->create();

        $this->delete("/animals/{$animal->id}")->assertRedirect();

        $this->assertDatabaseMissing('animals', ['id' => $animal->id]);
    }

    public function test_admin_can_delete_expenses(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $expense = Expense::factory()->create();

        $this->delete("/expenses/{$expense->id}")->assertRedirect();

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_guests_cannot_open_user_management(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }
}
