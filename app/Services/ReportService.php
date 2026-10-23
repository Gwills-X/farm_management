<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\AnimalType;
use App\Models\Expense;
use App\Models\FeedingRecord;
use App\Models\HealthRecord;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

/**
 * Builds each report from the same tables the rest of the app uses.
 * Output shape: ['title', 'columns' => [['key','label','type']], 'rows' => [...], 'summary' => [['label','value','type']]]
 * Types: text | date | number | money. Money values are decimal strings.
 */
class ReportService
{
    public const DEFINITIONS = [
        'animal_register' => ['label' => 'Animal register', 'filters' => ['animal_type_id', 'status', 'from', 'to']],
        'animals_by_type' => ['label' => 'Animals by type and status', 'filters' => ['animal_type_id']],
        'health_history' => ['label' => 'Health history', 'filters' => ['animal_type_id', 'health_status', 'from', 'to']],
        'feeding_history' => ['label' => 'Feeding history', 'filters' => ['animal_type_id', 'from', 'to']],
        'acquisitions' => ['label' => 'Acquisition records', 'filters' => ['animal_type_id', 'acquisition_type', 'from', 'to']],
        'sales' => ['label' => 'Sales report', 'filters' => ['payment_status', 'from', 'to']],
        'expenses' => ['label' => 'Expense report', 'filters' => ['expense_category_id', 'from', 'to']],
    ];

    private const STATUS = ['active' => 'Active', 'sold' => 'Sold', 'deceased' => 'Deceased', 'archived' => 'Archived'];
    private const SEX = ['male' => 'Male', 'female' => 'Female', 'unknown' => 'Unknown'];
    private const ACQUISITION = ['purchased' => 'Purchased', 'born_on_farm' => 'Born on farm', 'other' => 'Other'];
    private const HEALTH = ['healthy' => 'Healthy', 'monitoring' => 'Monitoring', 'sick' => 'Sick', 'recovered' => 'Recovered'];
    private const PAYMENT = ['unpaid' => 'Unpaid', 'partial' => 'Part paid', 'paid' => 'Paid'];
    private const METHOD = ['cash' => 'Cash', 'bank_transfer' => 'Bank transfer', 'pos' => 'POS', 'other' => 'Other'];

    public function build(string $type, array $filters): array
    {
        return match ($type) {
            'animal_register' => $this->animalRegister($filters),
            'animals_by_type' => $this->animalsByType($filters),
            'health_history' => $this->healthHistory($filters),
            'feeding_history' => $this->feedingHistory($filters),
            'acquisitions' => $this->acquisitions($filters),
            'sales' => $this->sales($filters),
            'expenses' => $this->expenses($filters),
            default => throw new InvalidArgumentException("Unknown report: {$type}"),
        };
    }

    private function label(?string $value, array $map): ?string
    {
        return $value === null ? null : ($map[$value] ?? $value);
    }

    private function dateRange(Builder $query, string $column, array $f): Builder
    {
        if (! empty($f['from'])) {
            $query->whereDate($column, '>=', $f['from']);
        }
        if (! empty($f['to'])) {
            $query->whereDate($column, '<=', $f['to']);
        }

        return $query;
    }

    private function report(string $title, array $columns, array $rows, array $summary): array
    {
        return compact('title', 'columns', 'rows', 'summary');
    }

    private function animalRegister(array $f): array
    {
        $query = Animal::query()->with('animalType:id,name')->orderBy('animal_code');

        if (! empty($f['animal_type_id'])) {
            $query->where('animal_type_id', $f['animal_type_id']);
        }
        if (! empty($f['status'])) {
            $query->where('status', $f['status']);
        }
        $this->dateRange($query, 'acquisition_date', $f);

        $rows = $query->get()->map(fn (Animal $a) => [
            'code' => $a->animal_code,
            'name' => $a->name,
            'type' => $a->animalType?->name,
            'sex' => $this->label($a->sex, self::SEX),
            'breed' => $a->breed,
            'status' => $this->label($a->status, self::STATUS),
            'acquisition' => $this->label($a->acquisition_type, self::ACQUISITION),
            'acquired' => $a->acquisition_date?->toDateString(),
            'weight' => $a->weight_kg,
        ])->all();

        return $this->report('Animal register', [
            ['key' => 'code', 'label' => 'Code', 'type' => 'text'],
            ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
            ['key' => 'type', 'label' => 'Type', 'type' => 'text'],
            ['key' => 'sex', 'label' => 'Sex', 'type' => 'text'],
            ['key' => 'breed', 'label' => 'Breed', 'type' => 'text'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
            ['key' => 'acquisition', 'label' => 'Acquisition', 'type' => 'text'],
            ['key' => 'acquired', 'label' => 'Acquired', 'type' => 'date'],
            ['key' => 'weight', 'label' => 'Weight (kg)', 'type' => 'number'],
        ], $rows, [
            ['label' => 'Animals listed', 'value' => count($rows), 'type' => 'number'],
        ]);
    }

    private function animalsByType(array $f): array
    {
        $types = AnimalType::query()
            ->when(! empty($f['animal_type_id']), fn ($q) => $q->where('id', $f['animal_type_id']))
            ->orderBy('name')
            ->get(['id', 'name']);

        $counts = Animal::query()
            ->selectRaw('animal_type_id, status, COUNT(*) AS total')
            ->groupBy('animal_type_id', 'status')
            ->get();

        $rows = $types->map(function (AnimalType $type) use ($counts) {
            $byStatus = $counts->where('animal_type_id', $type->id)->pluck('total', 'status');

            return [
                'type' => $type->name,
                'active' => (int) ($byStatus['active'] ?? 0),
                'sold' => (int) ($byStatus['sold'] ?? 0),
                'deceased' => (int) ($byStatus['deceased'] ?? 0),
                'archived' => (int) ($byStatus['archived'] ?? 0),
                'total' => (int) $byStatus->sum(),
            ];
        })->values()->all();

        return $this->report('Animals grouped by type and status', [
            ['key' => 'type', 'label' => 'Type', 'type' => 'text'],
            ['key' => 'active', 'label' => 'Active', 'type' => 'number'],
            ['key' => 'sold', 'label' => 'Sold', 'type' => 'number'],
            ['key' => 'deceased', 'label' => 'Deceased', 'type' => 'number'],
            ['key' => 'archived', 'label' => 'Archived', 'type' => 'number'],
            ['key' => 'total', 'label' => 'Total', 'type' => 'number'],
        ], $rows, [
            ['label' => 'Animals counted', 'value' => array_sum(array_column($rows, 'total')), 'type' => 'number'],
        ]);
    }

    private function healthHistory(array $f): array
    {
        $query = HealthRecord::query()
            ->with(['animal:id,animal_code,name,animal_type_id', 'animal.animalType:id,name', 'recordedBy:id,name'])
            ->orderByDesc('observation_date')
            ->orderByDesc('id');

        if (! empty($f['health_status'])) {
            $query->where('health_status', $f['health_status']);
        }
        if (! empty($f['animal_type_id'])) {
            $query->whereHas('animal', fn ($a) => $a->where('animal_type_id', $f['animal_type_id']));
        }
        $this->dateRange($query, 'observation_date', $f);

        $rows = $query->get()->map(fn (HealthRecord $r) => [
            'date' => $r->observation_date?->toDateString(),
            'code' => $r->animal?->animal_code,
            'name' => $r->animal?->name,
            'type' => $r->animal?->animalType?->name,
            'condition' => $r->observed_condition,
            'status' => $this->label($r->health_status, self::HEALTH),
            'treatment' => $r->treatment,
            'medication' => $r->medication,
            'vet' => $r->veterinary_professional,
            'follow_up' => $r->follow_up_date?->toDateString(),
            'recorded_by' => $r->recordedBy?->name,
        ])->all();

        return $this->report('Animal health history', [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'code', 'label' => 'Code', 'type' => 'text'],
            ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
            ['key' => 'type', 'label' => 'Type', 'type' => 'text'],
            ['key' => 'condition', 'label' => 'Condition', 'type' => 'text'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
            ['key' => 'treatment', 'label' => 'Treatment', 'type' => 'text'],
            ['key' => 'medication', 'label' => 'Medication', 'type' => 'text'],
            ['key' => 'vet', 'label' => 'Veterinary professional', 'type' => 'text'],
            ['key' => 'follow_up', 'label' => 'Follow-up', 'type' => 'date'],
            ['key' => 'recorded_by', 'label' => 'Recorded by', 'type' => 'text'],
        ], $rows, [
            ['label' => 'Observations', 'value' => count($rows), 'type' => 'number'],
        ]);
    }

    private function feedingHistory(array $f): array
    {
        $query = FeedingRecord::query()
            ->with(['animal:id,animal_code,name,animal_type_id', 'animal.animalType:id,name', 'recordedBy:id,name'])
            ->orderByDesc('fed_at')
            ->orderByDesc('id');

        if (! empty($f['animal_type_id'])) {
            $query->whereHas('animal', fn ($a) => $a->where('animal_type_id', $f['animal_type_id']));
        }
        $this->dateRange($query, 'fed_at', $f);

        $rows = $query->get()->map(fn (FeedingRecord $r) => [
            'fed_at' => $r->fed_at?->format('Y-m-d H:i'),
            'code' => $r->animal?->animal_code,
            'name' => $r->animal?->name,
            'type' => $r->animal?->animalType?->name,
            'feed' => $r->feed_type,
            'quantity' => trim($r->quantity.' '.$r->unit),
            'recorded_by' => $r->recordedBy?->name,
        ])->all();

        return $this->report('Feeding history', [
            ['key' => 'fed_at', 'label' => 'Fed at', 'type' => 'text'],
            ['key' => 'code', 'label' => 'Code', 'type' => 'text'],
            ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
            ['key' => 'type', 'label' => 'Type', 'type' => 'text'],
            ['key' => 'feed', 'label' => 'Feed', 'type' => 'text'],
            ['key' => 'quantity', 'label' => 'Quantity', 'type' => 'text'],
            ['key' => 'recorded_by', 'label' => 'Recorded by', 'type' => 'text'],
        ], $rows, [
            ['label' => 'Feeding records', 'value' => count($rows), 'type' => 'number'],
        ]);
    }

    private function acquisitions(array $f): array
    {
        $query = Animal::query()
            ->with(['animalType:id,name', 'purchaseItem.purchase:id,reference'])
            ->orderBy('acquisition_date')
            ->orderBy('id');

        if (! empty($f['animal_type_id'])) {
            $query->where('animal_type_id', $f['animal_type_id']);
        }
        if (! empty($f['acquisition_type'])) {
            $query->where('acquisition_type', $f['acquisition_type']);
        }
        $this->dateRange($query, 'acquisition_date', $f);

        $animals = $query->get();

        $rows = $animals->map(fn (Animal $a) => [
            'code' => $a->animal_code,
            'name' => $a->name,
            'type' => $a->animalType?->name,
            'acquisition' => $this->label($a->acquisition_type, self::ACQUISITION),
            'acquired' => $a->acquisition_date?->toDateString(),
            'source' => $a->source,
            'purchase_ref' => $a->purchaseItem?->purchase?->reference,
            'unit_cost' => $a->purchaseItem ? (string) $a->purchaseItem->unit_cost : null,
        ])->all();

        $purchasedCost = $animals->sum(fn (Animal $a) => (float) ($a->purchaseItem?->unit_cost ?? 0));

        return $this->report('Acquisition records', [
            ['key' => 'code', 'label' => 'Code', 'type' => 'text'],
            ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
            ['key' => 'type', 'label' => 'Type', 'type' => 'text'],
            ['key' => 'acquisition', 'label' => 'Acquisition', 'type' => 'text'],
            ['key' => 'acquired', 'label' => 'Acquired', 'type' => 'date'],
            ['key' => 'source', 'label' => 'Source / seller', 'type' => 'text'],
            ['key' => 'purchase_ref', 'label' => 'Purchase ref', 'type' => 'text'],
            ['key' => 'unit_cost', 'label' => 'Unit cost', 'type' => 'money'],
        ], $rows, [
            ['label' => 'Animals acquired', 'value' => count($rows), 'type' => 'number'],
            ['label' => 'Recorded purchase cost', 'value' => number_format($purchasedCost, 2, '.', ''), 'type' => 'money'],
        ]);
    }

    private function sales(array $f): array
    {
        $query = Sale::query()
            ->withCount('items')
            ->orderByDesc('sale_date')
            ->orderByDesc('id');

        if (! empty($f['payment_status'])) {
            $query->where('payment_status', $f['payment_status']);
        }
        $this->dateRange($query, 'sale_date', $f);

        $sales = $query->get();

        $rows = $sales->map(fn (Sale $s) => [
            'reference' => $s->reference,
            'date' => $s->sale_date?->toDateString(),
            'buyer' => $s->buyer_name,
            'animals' => $s->items_count,
            'total' => (string) $s->total_amount,
            'paid' => (string) $s->amount_paid,
            'outstanding' => number_format(max(0, (float) $s->total_amount - (float) $s->amount_paid), 2, '.', ''),
            'payment_status' => $this->label($s->payment_status, self::PAYMENT),
            'method' => $this->label($s->payment_method, self::METHOD),
        ])->all();

        $sold = $sales->sum(fn (Sale $s) => (float) $s->total_amount);
        $received = $sales->sum(fn (Sale $s) => (float) $s->amount_paid);

        return $this->report('Sales report', [
            ['key' => 'reference', 'label' => 'Reference', 'type' => 'text'],
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'buyer', 'label' => 'Buyer', 'type' => 'text'],
            ['key' => 'animals', 'label' => 'Animals', 'type' => 'number'],
            ['key' => 'total', 'label' => 'Total', 'type' => 'money'],
            ['key' => 'paid', 'label' => 'Paid', 'type' => 'money'],
            ['key' => 'outstanding', 'label' => 'Outstanding', 'type' => 'money'],
            ['key' => 'payment_status', 'label' => 'Payment', 'type' => 'text'],
            ['key' => 'method', 'label' => 'Method', 'type' => 'text'],
        ], $rows, [
            ['label' => 'Sales value', 'value' => number_format($sold, 2, '.', ''), 'type' => 'money'],
            ['label' => 'Received', 'value' => number_format($received, 2, '.', ''), 'type' => 'money'],
            ['label' => 'Outstanding', 'value' => number_format(max(0, $sold - $received), 2, '.', ''), 'type' => 'money'],
        ]);
    }

    private function expenses(array $f): array
    {
        $query = Expense::query()
            ->with(['category:id,name', 'recordedBy:id,name'])
            ->orderByDesc('expense_date')
            ->orderByDesc('id');

        if (! empty($f['expense_category_id'])) {
            $query->where('expense_category_id', $f['expense_category_id']);
        }
        $this->dateRange($query, 'expense_date', $f);

        $expenses = $query->get();

        $rows = $expenses->map(fn (Expense $e) => [
            'date' => $e->expense_date?->toDateString(),
            'category' => $e->category?->name,
            'description' => $e->description,
            'amount' => (string) $e->amount,
            'recorded_by' => $e->recordedBy?->name,
        ])->all();

        return $this->report('Expense report', [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['key' => 'description', 'label' => 'Description', 'type' => 'text'],
            ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'],
            ['key' => 'recorded_by', 'label' => 'Recorded by', 'type' => 'text'],
        ], $rows, [
            ['label' => 'Total expenses', 'value' => number_format((float) $expenses->sum('amount'), 2, '.', ''), 'type' => 'money'],
        ]);
    }
}
