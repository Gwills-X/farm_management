<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\AnimalType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnimalTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_animal_type_with_uppercase_prefix(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->post('/animal-types', ['name' => 'Rabbits', 'code_prefix' => 'rab', 'is_active' => true])
            ->assertRedirect(route('animal-types.index'));

        $this->assertDatabaseHas('animal_types', ['name' => 'Rabbits', 'code_prefix' => 'RAB', 'slug' => 'rabbits']);
    }

    public function test_duplicate_name_is_rejected(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        AnimalType::factory()->create(['name' => 'Goats']);

        $this->post('/animal-types', ['name' => 'Goats', 'code_prefix' => 'GTX', 'is_active' => true])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('animal_types', 1);
    }

    public function test_prefix_cannot_change_once_animals_exist(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $type = AnimalType::factory()->create(['code_prefix' => 'GOA', 'name' => 'Goats']);
        Animal::factory()->create(['animal_type_id' => $type->id]);

        $this->put("/animal-types/{$type->id}", [
            'name' => 'Goats',
            'code_prefix' => 'GTS',
            'is_active' => true,
        ])->assertSessionHasErrors('code_prefix');

        $this->assertDatabaseHas('animal_types', ['id' => $type->id, 'code_prefix' => 'GOA']);
    }

    public function test_name_can_change_and_prefix_can_stay_when_animals_exist(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $type = AnimalType::factory()->create(['code_prefix' => 'GOA', 'name' => 'Goats']);
        Animal::factory()->create(['animal_type_id' => $type->id]);

        $this->put("/animal-types/{$type->id}", [
            'name' => 'Dairy goats',
            'code_prefix' => 'GOA',
            'is_active' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('animal_types', ['id' => $type->id, 'name' => 'Dairy goats']);
    }

    public function test_inactive_type_is_not_offered_when_registering(): void
    {
        $this->actingAs(User::factory()->create());
        AnimalType::factory()->create(['name' => 'Active one']);
        AnimalType::factory()->inactive()->create(['name' => 'Retired one']);

        $this->get('/animals/create')->assertInertia(fn (Assert $page) => $page
            ->component('Animals/Form')
            ->has('animalTypes', 1)
            ->where('animalTypes.0.name', 'Active one'));
    }

    public function test_registration_is_refused_for_inactive_type(): void
    {
        $this->actingAs(User::factory()->create());
        $type = AnimalType::factory()->inactive()->create();

        $this->post('/animals', [
            'animal_type_id' => $type->id,
            'sex' => 'unknown',
            'acquisition_type' => 'born_on_farm',
            'acquisition_date' => now()->toDateString(),
        ])->assertSessionHasErrors('animal_type_id');

        $this->assertDatabaseCount('animals', 0);
    }
}
