<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\AnimalType;
use App\Models\Purchase;
use App\Services\PurchaseService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $purchases)
    {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Purchase::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $search = $filters['search'] ?? '';
        $from = $filters['from'] ?? '';
        $to = $filters['to'] ?? '';

        $query = Purchase::query()
            ->withCount('items')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference', 'like', '%'.$search.'%')
                        ->orWhere('seller_name', 'like', '%'.$search.'%');
                });
            })
            ->when($from !== '', fn ($q) => $q->whereDate('purchase_date', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('purchase_date', '<=', $to));

        $totalCost = (clone $query)->sum('total_cost');

        $purchases = $query->latest('purchase_date')->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
            'filters' => compact('search', 'from', 'to'),
            'totalCost' => number_format((float) $totalCost, 2, '.', ''),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Purchase::class);

        return Inertia::render('Purchases/Create', [
            'animalTypes' => AnimalType::active()->orderBy('name')->get(['id', 'name', 'code_prefix']),
        ]);
    }

    public function store(PurchaseRequest $request): RedirectResponse
    {
        $this->authorize('create', Purchase::class);

        try {
            $purchase = $this->purchases->record($request->validated(), $request->user()->id);
        } catch (DomainException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', "Purchase {$purchase->reference} recorded and animals registered.");
    }

    public function show(Purchase $purchase): Response
    {
        $this->authorize('view', $purchase);

        $purchase->load([
            'recordedBy:id,name',
            'items.animal:id,animal_code,name,animal_type_id,status',
            'items.animal.animalType:id,name',
        ]);

        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }
}
