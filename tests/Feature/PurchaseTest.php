<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\AnimalType;
use App\Models\User;
use App\Services\AnimalRegistrationService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_registers_each_animal_as_purchased(): void
    {
        $this->actingAs(User::factory()->create());
        $type = AnimalType::factory()->create();

        $this->post('/purchases', [
            'purchase_date' => now()->toDateString(),
            'seller_name' => 'Ali Musa',
            'items' => [
                ['animal_type_id' => $type->id, 'sex' => 'female', 'unit_cost' => 50000],
                ['animal_type_id' => $type->id, 'sex' => 'male', 'unit_cost' => 70000],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('purchases', ['seller_name' => 'Ali Musa', 'total_cost' => 120000]);
        $this->assertDatabaseCount('animals', 2);
        $this->assertDatabaseCount('purchase_items', 2);
        $this->assertDatabaseHas('animals', ['acquisition_type' => 'purchased', 'source' => 'Ali Musa']);
    }

    public function test_inactive_type_is_refused_in_a_purchase(): void
    {
        $this->actingAs(User::factory()->create());
        $type = AnimalType::factory()->inactive()->create();

        $this->post('/purchases', [
            'purchase_date' => now()->toDateString(),
            'items' => [['animal_type_id' => $type->id, 'sex' => 'female', 'unit_cost' => 1000]],
        ])->assertSessionHasErrors('items.0.animal_type_id');

        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_failed_purchase_leaves_no_partial_records(): void
    {
        $this->actingAs(User::factory()->create());
        $type = AnimalType::factory()->create();

        // The first animal registers fine; the second fails. Everything must roll back.
        $this->mock(AnimalRegistrationService::class, function (MockInterface $mock) use ($type) {
            $calls = 0;
            $mock->shouldReceive('register')->andReturnUsing(function () use (&$calls, $type) {
                $calls++;
                if ($calls === 2) {
                    throw new DomainException('Simulated failure on second animal.');
                }

                return Animal::factory()->create(['animal_type_id' => $type->id]);
            });
        });

        $this->post('/purchases', [
            'purchase_date' => now()->toDateString(),
            'items' => [
                ['animal_type_id' => $type->id, 'sex' => 'female', 'unit_cost' => 1000],
                ['animal_type_id' => $type->id, 'sex' => 'male', 'unit_cost' => 2000],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('purchase_items', 0);
        $this->assertDatabaseCount('animals', 0);
    }
}
