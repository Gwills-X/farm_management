<?php

namespace Tests\Feature;

use App\Models\AnimalType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimalRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(AnimalType $type, array $extra = []): array
    {
        return array_merge([
            'animal_type_id' => $type->id,
            'name' => 'Test animal',
            'sex' => 'female',
            'acquisition_type' => 'born_on_farm',
            'acquisition_date' => now()->toDateString(),
        ], $extra);
    }

    public function test_animal_gets_a_code_with_the_type_prefix(): void
    {
        $this->actingAs(User::factory()->create());
        $type = AnimalType::factory()->create(['code_prefix' => 'GOA']);

        $this->post('/animals', $this->payload($type))->assertRedirect();

        $this->assertDatabaseHas('animals', ['animal_code' => 'GOA-00001', 'animal_type_id' => $type->id]);
    }

    public function test_codes_increase_per_type(): void
    {
        $this->actingAs(User::factory()->create());
        $goats = AnimalType::factory()->create(['code_prefix' => 'GOA']);
        $cattle = AnimalType::factory()->create(['code_prefix' => 'CAT']);

        $this->post('/animals', $this->payload($goats));
        $this->post('/animals', $this->payload($goats));
        $this->post('/animals', $this->payload($cattle));

        $this->assertDatabaseHas('animals', ['animal_code' => 'GOA-00002']);
        $this->assertDatabaseHas('animals', ['animal_code' => 'CAT-00001']);
        $this->assertDatabaseHas('animal_types', ['id' => $goats->id, 'code_sequence' => 2]);
    }

    public function test_client_supplied_code_is_ignored(): void
    {
        $this->actingAs(User::factory()->create());
        $type = AnimalType::factory()->create(['code_prefix' => 'GOA']);

        $this->post('/animals', $this->payload($type, ['animal_code' => 'HACK-99']))->assertRedirect();

        $this->assertDatabaseMissing('animals', ['animal_code' => 'HACK-99']);
        $this->assertDatabaseHas('animals', ['animal_code' => 'GOA-00001']);
    }

    public function test_nonexistent_type_is_refused(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/animals', [
            'animal_type_id' => 99999,
            'sex' => 'female',
            'acquisition_type' => 'born_on_farm',
            'acquisition_date' => now()->toDateString(),
        ])->assertSessionHasErrors('animal_type_id');

        $this->assertDatabaseCount('animals', 0);
    }
}
