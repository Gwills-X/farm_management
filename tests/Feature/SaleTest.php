<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\AnimalType;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    private function salePayload(array $items, ?string $amountPaid = null): array
    {
        $payload = [
            'sale_date' => now()->toDateString(),
            'buyer_name' => 'Musa Garba',
            'payment_method' => 'cash',
            'items' => $items,
        ];

        if ($amountPaid !== null) {
            $payload['amount_paid'] = $amountPaid;
        }

        return $payload;
    }

    public function test_sales_index_loads_with_aggregate_totals(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/sales')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Sales/Index')
                ->where('totals.sold', '0.00')
                ->where('totals.received', '0.00')
            );
    }

    public function test_sale_marks_animals_sold_and_calculates_totals(): void
    {
        $this->actingAs(User::factory()->create());
        $type = AnimalType::factory()->create();
        $a1 = Animal::factory()->create(['animal_type_id' => $type->id]);
        $a2 = Animal::factory()->create(['animal_type_id' => $type->id]);

        $this->post('/sales', $this->salePayload([
            ['animal_id' => $a1->id, 'unit_price' => 100000],
            ['animal_id' => $a2->id, 'unit_price' => 50000],
        ], '60000'))->assertRedirect();

        $this->assertDatabaseHas('sales', [
            'buyer_name' => 'Musa Garba',
            'total_amount' => 150000,
            'amount_paid' => 60000,
            'payment_status' => 'partial',
        ]);
        $this->assertDatabaseHas('animals', ['id' => $a1->id, 'status' => 'sold']);
        $this->assertDatabaseHas('animals', ['id' => $a2->id, 'status' => 'sold']);
        $this->assertDatabaseCount('sale_payments', 1);
    }

    public function test_sold_animal_cannot_be_sold_again(): void
    {
        $this->actingAs(User::factory()->create());
        $animal = Animal::factory()->create();

        $this->post('/sales', $this->salePayload([['animal_id' => $animal->id, 'unit_price' => 1000]]))
            ->assertRedirect();
        $this->post('/sales', $this->salePayload([['animal_id' => $animal->id, 'unit_price' => 1000]]))
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('sales', 1);
    }

    public function test_failed_sale_rolls_back_and_keeps_other_animals_active(): void
    {
        $this->actingAs(User::factory()->create());
        $active = Animal::factory()->create();
        $deceased = Animal::factory()->deceased()->create();

        $this->post('/sales', $this->salePayload([
            ['animal_id' => $active->id, 'unit_price' => 1000],
            ['animal_id' => $deceased->id, 'unit_price' => 1000],
        ]))->assertSessionHasErrors('items');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseHas('animals', ['id' => $active->id, 'status' => 'active']);
    }

    public function test_payments_move_sale_to_paid(): void
    {
        $this->actingAs(User::factory()->create());
        $animal = Animal::factory()->create();

        $this->post('/sales', $this->salePayload([['animal_id' => $animal->id, 'unit_price' => 100000]]));
        $sale = Sale::query()->firstOrFail();
        $this->assertSame('unpaid', $sale->payment_status);

        $this->post("/sales/{$sale->id}/payments", ['amount' => 40000, 'paid_on' => now()->toDateString()]);
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'payment_status' => 'partial']);

        $this->post("/sales/{$sale->id}/payments", ['amount' => 60000, 'paid_on' => now()->toDateString()]);
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'payment_status' => 'paid', 'amount_paid' => 100000]);
        $this->assertDatabaseCount('sale_payments', 2);
    }

    public function test_payment_above_outstanding_balance_is_refused(): void
    {
        $this->actingAs(User::factory()->create());
        $animal = Animal::factory()->create();

        $this->post('/sales', $this->salePayload([['animal_id' => $animal->id, 'unit_price' => 100000]]), );
        $sale = Sale::query()->firstOrFail();

        $this->post("/sales/{$sale->id}/payments", ['amount' => 100001, 'paid_on' => now()->toDateString()])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'amount_paid' => 0]);
    }
}
