<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Expense::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $search = $filters['search'] ?? '';
        $categoryId = $filters['expense_category_id'] ?? '';
        $from = $filters['from'] ?? '';
        $to = $filters['to'] ?? '';

        $query = Expense::query()
            ->when($search !== '', fn ($q) => $q->where('description', 'like', '%'.$search.'%'))
            ->when($categoryId !== '', fn ($q) => $q->where('expense_category_id', $categoryId))
            ->when($from !== '', fn ($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('expense_date', '<=', $to));

        $total = (clone $query)->sum('amount');

        $expenses = $query->with(['category:id,name', 'recordedBy:id,name'])
            ->latest('expense_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'filters' => compact('search', 'from', 'to') + ['expense_category_id' => $categoryId],
            'categories' => ExpenseCategory::orderBy('name')->get(['id', 'name', 'is_active']),
            'total' => number_format((float) $total, 2, '.', ''),
            'canDelete' => $request->user()->isAdmin(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Expense::class);

        return Inertia::render('Expenses/Form', [
            'expense' => null,
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $this->authorize('create', Expense::class);

        Expense::create($request->validated() + ['recorded_by' => $request->user()->id]);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense recorded.');
    }

    public function edit(Expense $expense): Response
    {
        $this->authorize('update', $expense);

        return Inertia::render('Expenses/Form', [
            'expense' => $expense,
            'categories' => ExpenseCategory::orderBy('name')->get(['id', 'name', 'is_active']),
        ]);
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $expense->update($request->validated());

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted.');
    }
}
