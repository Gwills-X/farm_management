<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_database_renders_without_errors(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('animals.total', 0)
            ->where('money.sales_value', '0.00'));
    }

    public function test_animal_counts_match_the_database(): void
    {
        $this->actingAs(User::factory()->create());
        Animal::factory()->count(2)->create();
        Animal::factory()->sold()->create();

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('animals.total', 3)
            ->where('animals.active', 2)
            ->where('animals.sold', 1));
    }

    public function test_money_figures_are_not_netted_into_profit(): void
    {
        $this->actingAs(User::factory()->create());
        Sale::factory()->create([
            'sale_date' => now()->toDateString(),
            'total_amount' => 100000,
            'amount_paid' => 40000,
            'payment_status' => 'partial',
        ]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('money.sales_value', '100000.00')
            ->where('money.received', '40000.00')
            ->where('money.outstanding', '60000.00')
            ->missing('money.profit'));
    }
}
