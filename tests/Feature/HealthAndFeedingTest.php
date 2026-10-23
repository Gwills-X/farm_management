<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\FeedingRecord;
use App\Models\HealthRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthAndFeedingTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_record_links_to_animal_and_recorder(): void
    {
        $staff = User::factory()->create();
        $this->actingAs($staff);
        $animal = Animal::factory()->create();

        $this->post("/animals/{$animal->id}/health-records", [
            'observation_date' => now()->toDateString(),
            'observed_condition' => 'Limping on left leg',
            'health_status' => 'monitoring',
        ])->assertRedirect();

        $this->assertDatabaseHas('health_records', [
            'animal_id' => $animal->id,
            'recorded_by' => $staff->id,
            'health_status' => 'monitoring',
        ]);
    }

    public function test_staff_cannot_edit_another_users_health_record(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $record = HealthRecord::factory()->create(['recorded_by' => $owner->id]);

        $this->actingAs($other);

        $this->put("/health-records/{$record->id}", [
            'observation_date' => now()->toDateString(),
            'observed_condition' => 'Changed by someone else',
            'health_status' => 'healthy',
        ])->assertForbidden();

        $this->assertDatabaseMissing('health_records', ['observed_condition' => 'Changed by someone else']);
    }

    public function test_feeding_is_recorded_for_an_active_animal(): void
    {
        $this->actingAs(User::factory()->create());
        $animal = Animal::factory()->create();

        $this->post("/animals/{$animal->id}/feeding-records", [
            'fed_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'feed_type' => 'Maize bran',
            'quantity' => 3,
            'unit' => 'kg',
        ])->assertRedirect();

        $this->assertDatabaseHas('feeding_records', ['animal_id' => $animal->id, 'feed_type' => 'Maize bran']);
    }

    public function test_feeding_is_refused_for_a_sold_animal(): void
    {
        $this->actingAs(User::factory()->create());
        $animal = Animal::factory()->sold()->create();

        $this->post("/animals/{$animal->id}/feeding-records", [
            'fed_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'feed_type' => 'Maize bran',
            'quantity' => 3,
            'unit' => 'kg',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('feeding_records', 0);
    }
}
