<?php

namespace App\Http\Controllers;

use App\Http\Requests\HealthRecordRequest;
use App\Models\Animal;
use App\Models\HealthRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HealthRecordController extends Controller
{
    private const STATUSES = ['healthy', 'monitoring', 'sick', 'recovered'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', HealthRecord::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $search = $filters['search'] ?? '';
        $status = $filters['status'] ?? '';
        $from = $filters['from'] ?? '';
        $to = $filters['to'] ?? '';

        $records = HealthRecord::query()
            ->with(['animal:id,animal_code,name', 'recordedBy:id,name'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('animal', function ($animal) use ($search) {
                    $animal->where('animal_code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('health_status', $status))
            ->when($from !== '', fn ($query) => $query->whereDate('observation_date', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('observation_date', '<=', $to))
            ->latest('observation_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('HealthRecords/Index', [
            'records' => $records,
            'filters' => compact('search', 'status', 'from', 'to'),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(Animal $animal): Response|RedirectResponse
    {
        $this->authorize('create', HealthRecord::class);

        return Inertia::render('HealthRecords/Form', [
            'animal' => $animal->load('animalType:id,name'),
            'record' => null,
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(HealthRecordRequest $request, Animal $animal): RedirectResponse
    {
        $this->authorize('create', HealthRecord::class);

        $animal->healthRecords()->create($request->validated() + [
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('animals.show', $animal)
            ->with('success', 'Health observation saved.');
    }

    public function edit(HealthRecord $healthRecord): Response
    {
        $this->authorize('update', $healthRecord);

        $healthRecord->load('animal.animalType:id,name');

        return Inertia::render('HealthRecords/Form', [
            'animal' => $healthRecord->animal,
            'record' => $healthRecord,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(HealthRecordRequest $request, HealthRecord $healthRecord): RedirectResponse
    {
        $this->authorize('update', $healthRecord);

        $healthRecord->update($request->validated());

        return redirect()
            ->route('animals.show', $healthRecord->animal_id)
            ->with('success', 'Health observation updated.');
    }
}
