<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\HealthRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimalStatusTest extends TestCase
{
    use RefreshDatabase;

    private function updatePayload(Animal $animal, string $status): array
    {
        return [
            'sex' => $animal->sex,
            'acquisition_type' => $animal->acquisition_type,
            'acquisition_date' => $animal->acquisition_date->toDateString(),
            'status' => $status,
        ];
    }

    public function test_deceased_animal_cannot_return_to_active(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $animal = Animal::factory()->deceased()->create();

        $this->put("/animals/{$animal->id}", $this->updatePayload($animal, 'active'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('animals', ['id' => $animal->id, 'status' => 'deceased']);
    }

    public function test_archived_animal_can_return_to_active(): void
    {
        $this->actingAs(User::factory()->create());
        $animal = Animal::factory()->archived()->create();

        $this->put("/animals/{$animal->id}", $this->updatePayload($animal, 'active'))->assertRedirect();

        $this->assertDatabaseHas('animals', ['id' => $animal->id, 'status' => 'active']);
    }

    public function test_sold_animal_status_cannot_be_edited(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $animal = Animal::factory()->sold()->create();

        $this->put("/animals/{$animal->id}", $this->updatePayload($animal, 'active'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('animals', ['id' => $animal->id, 'status' => 'sold']);
    }

    public function test_animal_with_health_history_cannot_be_deleted(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $animal = Animal::factory()->create();
        HealthRecord::factory()->create(['animal_id' => $animal->id]);

        $this->delete("/animals/{$animal->id}")->assertSessionHas('error');

        $this->assertDatabaseHas('animals', ['id' => $animal->id]);
    }
}
