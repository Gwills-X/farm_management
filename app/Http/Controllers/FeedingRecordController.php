<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeedingRecordRequest;
use App\Models\Animal;
use App\Models\AnimalType;
use App\Models\FeedingRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FeedingRecordController extends Controller
{
    private const UNITS = ['kg', 'g', 'litre', 'bag', 'other'];

    /** Feed cannot be recorded for animals that have left the farm. */
    private const NOT_FEEDABLE = [Animal::STATUS_SOLD, Animal::STATUS_DECEASED];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FeedingRecord::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'animal_type_id' => ['nullable', 'integer', 'exists:animal_types,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $search = $filters['search'] ?? '';
        $typeId = $filters['animal_type_id'] ?? '';
        $from = $filters['from'] ?? '';
        $to = $filters['to'] ?? '';

        $records = FeedingRecord::query()
            ->with(['animal:id,animal_code,name,animal_type_id', 'animal.animalType:id,name', 'recordedBy:id,name'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('animal', function ($animal) use ($search) {
                    $animal->where('animal_code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%');
                });
            })
            ->when($typeId !== '', function ($query) use ($typeId) {
                $query->whereHas('animal', fn ($animal) => $animal->where('animal_type_id', $typeId));
            })
            ->when($from !== '', fn ($query) => $query->whereDate('fed_at', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('fed_at', '<=', $to))
            ->latest('fed_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('FeedingRecords/Index', [
            'records' => $records,
            'filters' => compact('search', 'from', 'to') + ['animal_type_id' => $typeId],
            'animalTypes' => AnimalType::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Animal $animal): Response|RedirectResponse
    {
        $this->authorize('create', FeedingRecord::class);

        if (in_array($animal->status, self::NOT_FEEDABLE, true)) {
            return redirect()
                ->route('animals.show', $animal)
                ->with('error', "Feeding cannot be recorded for a {$animal->status} animal.");
        }

        return Inertia::render('FeedingRecords/Form', [
            'animal' => $animal->load('animalType:id,name'),
            'record' => null,
            'units' => self::UNITS,
        ]);
    }

    public function store(FeedingRecordRequest $request, Animal $animal): RedirectResponse
    {
        $this->authorize('create', FeedingRecord::class);

        if (in_array($animal->status, self::NOT_FEEDABLE, true)) {
            return redirect()
                ->route('animals.show', $animal)
                ->with('error', "Feeding cannot be recorded for a {$animal->status} animal.");
        }

        $animal->feedingRecords()->create($request->validated() + [
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('animals.show', $animal)
            ->with('success', 'Feeding record saved.');
    }

    public function edit(FeedingRecord $feedingRecord): Response
    {
        $this->authorize('update', $feedingRecord);

        $feedingRecord->load('animal.animalType:id,name');

        return Inertia::render('FeedingRecords/Form', [
            'animal' => $feedingRecord->animal,
            'record' => $feedingRecord,
            'units' => self::UNITS,
        ]);
    }

    public function update(FeedingRecordRequest $request, FeedingRecord $feedingRecord): RedirectResponse
    {
        $this->authorize('update', $feedingRecord);

        $feedingRecord->update($request->validated());

        return redirect()
            ->route('animals.show', $feedingRecord->animal_id)
            ->with('success', 'Feeding record updated.');
    }
}
