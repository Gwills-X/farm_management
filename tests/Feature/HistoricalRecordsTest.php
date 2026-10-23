<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\AnimalType;
use App\Models\HealthRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivating_a_type_keeps_its_animals_and_history(): void
    {
        $admin = User::factory()->admin()->create();
        $type = AnimalType::factory()->create(['name' => 'Pigs', 'code_prefix' => 'PIG']);
        $animal = Animal::factory()->create(['animal_type_id' => $type->id]);
        $record = HealthRecord::factory()->create(['animal_id' => $animal->id]);

        $this->actingAs($admin)->put("/animal-types/{$type->id}", [
            'name' => 'Pigs',
            'code_prefix' => 'PIG',
            'is_active' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('animal_types', ['id' => $type->id, 'is_active' => false]);
        $this->assertDatabaseHas('animals', ['id' => $animal->id, 'status' => 'active']);
        $this->assertDatabaseHas('health_records', ['id' => $record->id]);

        $this->get("/animals/{$animal->id}")->assertOk();
    }
}
