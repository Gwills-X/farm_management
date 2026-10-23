<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_report_renders_on_an_empty_database(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (array_keys(ReportService::DEFINITIONS) as $type) {
            $this->get("/reports?type={$type}")
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page->component('Reports/Index')->where('type', $type));
        }
    }

    public function test_sales_report_summary_matches_sales(): void
    {
        $this->actingAs(User::factory()->create());
        Sale::factory()->create(['sale_date' => now()->toDateString(), 'total_amount' => 150000, 'amount_paid' => 60000]);
        Sale::factory()->create(['sale_date' => now()->toDateString(), 'total_amount' => 50000, 'amount_paid' => 0]);

        $this->get('/reports?type=sales')->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Index')
            ->where('report.summary.0.value', '200000.00')
            ->where('report.summary.1.value', '60000.00')
            ->where('report.summary.2.value', '140000.00'));
    }

    public function test_csv_export_returns_a_header_row_and_data(): void
    {
        $this->actingAs(User::factory()->create());
        $sale = Sale::factory()->create(['sale_date' => now()->toDateString(), 'reference' => 'SAL-TESTREF']);

        $response = $this->get('/reports?type=sales&export=csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('Reference', $content);
        $this->assertStringContainsString($sale->reference, $content);
    }
}
