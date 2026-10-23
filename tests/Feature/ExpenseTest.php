<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    private function payload(ExpenseCategory $category): array
    {
        return [
            'expense_category_id' => $category->id,
            'expense_date' => now()->toDateString(),
            'amount' => 7500,
            'description' => 'Bag of maize bran',
        ];
    }

    public function test_staff_records_an_expense(): void
    {
        $staff = User::factory()->create();
        $this->actingAs($staff);
        $category = ExpenseCategory::factory()->create();

        $this->post('/expenses', $this->payload($category))->assertRedirect();

        $this->assertDatabaseHas('expenses', ['amount' => 7500, 'recorded_by' => $staff->id]);
    }

    public function test_inactive_category_is_refused(): void
    {
        $this->actingAs(User::factory()->create());
        $category = ExpenseCategory::factory()->inactive()->create();

        $this->post('/expenses', $this->payload($category))->assertSessionHasErrors('expense_category_id');

        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_staff_can_edit_their_own_expense(): void
    {
        $staff = User::factory()->create();
        $this->actingAs($staff);
        $expense = Expense::factory()->create(['recorded_by' => $staff->id]);

        $this->put("/expenses/{$expense->id}", $this->payload($expense->category))->assertRedirect();

        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'amount' => 7500]);
    }

    public function test_staff_cannot_edit_someone_elses_expense(): void
    {
        $this->actingAs(User::factory()->create());
        $expense = Expense::factory()->create(['recorded_by' => User::factory()->create()->id]);

        $this->put("/expenses/{$expense->id}", $this->payload($expense->category))->assertForbidden();
    }

    public function test_totals_are_shown_for_the_filtered_list(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Expense::factory()->create(['amount' => 1000, 'expense_date' => now()->toDateString()]);
        Expense::factory()->create(['amount' => 2500, 'expense_date' => now()->subDays(40)->toDateString()]);

        $this->get('/expenses?from='.now()->startOfMonth()->toDateString())
            ->assertInertia(fn ($page) => $page->component('Expenses/Index')->where('total', '1000.00'));
    }
}
