<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\AnimalType;
use App\Models\Expense;
use App\Models\FeedingRecord;
use App\Models\HealthRecord;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now()->toDateString();

        $statusCounts = Animal::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $types = AnimalType::query()
            ->where('is_active', true)
            ->withCount([
                'animals as total_count',
                'animals as active_count' => fn ($q) => $q->where('status', Animal::STATUS_ACTIVE),
            ])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'total' => (int) $type->total_count,
                'active' => (int) $type->active_count,
            ])
            ->values();

        // Sick or monitoring observations from the last 30 days.
        $attention = HealthRecord::query()
            ->with('animal:id,animal_code,name')
            ->whereIn('health_status', ['sick', 'monitoring'])
            ->where('observation_date', '>=', now()->subDays(30)->toDateString())
            ->latest('observation_date')
            ->latest('id')
            ->limit(8)
            ->get();

        $recentFeeding = FeedingRecord::query()
            ->with('animal:id,animal_code,name')
            ->latest('fed_at')
            ->latest('id')
            ->limit(5)
            ->get();

        $recentSales = Sale::query()
            ->latest('sale_date')
            ->latest('id')
            ->limit(5)
            ->get(['id', 'reference', 'sale_date', 'buyer_name', 'total_amount', 'amount_paid', 'payment_status']);

        $salesValue = (float) Sale::whereBetween('sale_date', [$from, $to])->sum('total_amount');
        $received = (float) Sale::whereBetween('sale_date', [$from, $to])->sum('amount_paid');
        $expenses = (float) Expense::whereBetween('expense_date', [$from, $to])->sum('amount');
        $purchases = (float) Purchase::whereBetween('purchase_date', [$from, $to])->sum('total_cost');

        return Inertia::render('Dashboard', [
            'animals' => [
                'total' => (int) $statusCounts->sum(),
                'active' => (int) ($statusCounts['active'] ?? 0),
                'sold' => (int) ($statusCounts['sold'] ?? 0),
                'deceased' => (int) ($statusCounts['deceased'] ?? 0),
                'archived' => (int) ($statusCounts['archived'] ?? 0),
            ],
            'types' => $types,
            'attention' => $attention,
            'recentFeeding' => $recentFeeding,
            'recentSales' => $recentSales,
            'period' => ['from' => $from, 'to' => $to],
            // Money figures are kept separate. This is not a profit figure: acquisition
            // costs and other costs are not netted against sales here.
            'money' => [
                'sales_value' => number_format($salesValue, 2, '.', ''),
                'received' => number_format($received, 2, '.', ''),
                'outstanding' => number_format(max(0, $salesValue - $received), 2, '.', ''),
                'expenses' => number_format($expenses, 2, '.', ''),
                'purchases' => number_format($purchases, 2, '.', ''),
            ],
        ]);
    }
}
