<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalePaymentRequest;
use App\Http\Requests\SaleRequest;
use App\Models\Animal;
use App\Models\Sale;
use App\Services\SalePaymentService;
use App\Services\SaleService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SaleController extends Controller
{
    private const PAYMENT_STATUSES = ['unpaid', 'partial', 'paid'];

    public function __construct(
        private SaleService $sales,
        private SalePaymentService $payments,
    ) {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Sale::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'payment_status' => ['nullable', Rule::in(self::PAYMENT_STATUSES)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $search = $filters['search'] ?? '';
        $status = $filters['payment_status'] ?? '';
        $from = $filters['from'] ?? '';
        $to = $filters['to'] ?? '';

        $query = Sale::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference', 'like', '%'.$search.'%')
                        ->orWhere('buyer_name', 'like', '%'.$search.'%');
                });
            })
            ->when($status !== '', fn ($q) => $q->where('payment_status', $status))
            ->when($from !== '', fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('sale_date', '<=', $to));

        $totals = (clone $query)->selectRaw(
            'COALESCE(SUM(total_amount), 0) AS sold, COALESCE(SUM(amount_paid), 0) AS received'
        )->first();

        $sales = (clone $query)->withCount('items')->latest('sale_date')->latest('id')
            ->paginate(20)
            ->withQueryString();

        $sold = (float) $totals->sold;
        $received = (float) $totals->received;

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => compact('search', 'from', 'to') + ['payment_status' => $status],
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'totals' => [
                'sold' => number_format($sold, 2, '.', ''),
                'received' => number_format($received, 2, '.', ''),
                'outstanding' => number_format(max(0, $sold - $received), 2, '.', ''),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Sale::class);

        $animals = Animal::query()
            ->with('animalType:id,name')
            ->where('status', Animal::STATUS_ACTIVE)
            ->orderBy('animal_code')
            ->limit(500)
            ->get(['id', 'animal_code', 'name', 'animal_type_id']);

        return Inertia::render('Sales/Create', [
            'animals' => $animals,
            'paymentMethods' => ['cash', 'bank_transfer', 'pos', 'other'],
        ]);
    }

    public function store(SaleRequest $request): RedirectResponse
    {
        $this->authorize('create', Sale::class);

        try {
            $sale = $this->sales->record($request->validated(), $request->user()->id);
        } catch (DomainException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "Sale {$sale->reference} recorded. Animals marked as sold.");
    }

    public function show(Sale $sale): Response
    {
        $this->authorize('view', $sale);

        $sale->load([
            'recordedBy:id,name',
            'items.animal:id,animal_code,name,animal_type_id,status',
            'items.animal.animalType:id,name',
            'payments.recordedBy:id,name',
        ]);

        return Inertia::render('Sales/Show', [
            'sale' => $sale,
            'paymentMethods' => ['cash', 'bank_transfer', 'pos', 'other'],
        ]);
    }

    public function recordPayment(SalePaymentRequest $request, Sale $sale): RedirectResponse
    {
        $this->authorize('recordPayment', $sale);

        try {
            $this->payments->record(
                $sale,
                $request->validated('amount'),
                $request->validated('payment_method'),
                $request->validated('paid_on'),
                $request->user()->id,
            );
        } catch (DomainException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Payment recorded.');
    }
}
