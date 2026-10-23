<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnimalRequest;
use App\Http\Requests\UpdateAnimalRequest;
use App\Models\Animal;
use App\Models\AnimalType;
use App\Services\AnimalRegistrationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AnimalController extends Controller
{
    private const STATUSES = [
        Animal::STATUS_ACTIVE,
        Animal::STATUS_SOLD,
        Animal::STATUS_DECEASED,
        Animal::STATUS_ARCHIVED,
    ];

    public function __construct(private AnimalRegistrationService $registration)
    {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Animal::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'animal_type_id' => ['nullable', 'integer', 'exists:animal_types,id'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
        ]);

        $search = $filters['search'] ?? '';
        $typeId = $filters['animal_type_id'] ?? '';
        $status = $filters['status'] ?? '';

        $animals = Animal::query()
            ->with('animalType:id,name,code_prefix')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('animal_code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%');
                });
            })
            ->when($typeId !== '', fn ($query) => $query->where('animal_type_id', $typeId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Animals/Index', [
            'animals' => $animals,
            'filters' => [
                'search' => $search,
                'animal_type_id' => $typeId,
                'status' => $status,
            ],
            'animalTypes' => AnimalType::orderBy('name')->get(['id', 'name', 'is_active']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Animal::class);

        return Inertia::render('Animals/Form', [
            'animal' => null,
            'animalTypes' => AnimalType::active()->orderBy('name')->get(['id', 'name', 'code_prefix']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(StoreAnimalRequest $request): RedirectResponse
    {
        $this->authorize('create', Animal::class);

        try {
            $animal = $this->registration->register($request->validated(), $request->user()->id);
        } catch (DomainException $e) {
            return back()
                ->withErrors(['animal_type_id' => $e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('animals.show', $animal)
            ->with('success', "Animal {$animal->animal_code} registered.");
    }

    public function show(Animal $animal): Response
    {
        $this->authorize('view', $animal);

        $animal->load([
            'animalType:id,name,code_prefix',
            'purchaseItem.purchase:id,reference,purchase_date,seller_name',
            'saleItem.sale:id,reference,sale_date,buyer_name,total_amount,amount_paid,payment_status',
        ]);

        return Inertia::render('Animals/Show', [
            'animal' => $animal,
            'healthRecords' => $animal->healthRecords()->with('recordedBy:id,name')->take(10)->get(),
            'feedingRecords' => $animal->feedingRecords()->with('recordedBy:id,name')->take(10)->get(),
            'hasRecords' => $animal->hasRecords(),
        ]);
    }

    public function edit(Animal $animal): Response
    {
        $this->authorize('update', $animal);

        $animal->load('animalType:id,name,code_prefix');

        return Inertia::render('Animals/Form', [
            'animal' => $animal,
            'animalTypes' => AnimalType::orderBy('name')->get(['id', 'name', 'code_prefix']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(UpdateAnimalRequest $request, Animal $animal): RedirectResponse
    {
        $this->authorize('update', $animal);

        $data = $request->validated();

        if (array_key_exists('status', $data) && $data['status'] !== $animal->status) {
            if (! $animal->canTransitionTo($data['status'])) {
                return back()
                    ->withErrors(['status' => "A {$animal->status} animal cannot be changed to {$data['status']}."])
                    ->withInput();
            }
        }

        $animal->update($data);

        return redirect()
            ->route('animals.show', $animal)
            ->with('success', 'Animal updated.');
    }

    public function destroy(Animal $animal): RedirectResponse
    {
        $this->authorize('delete', $animal);

        if ($animal->hasRecords()) {
            return back()->with('error', 'This animal has farm records and cannot be deleted. Change its status to archived instead.');
        }

        $animal->delete();

        return redirect()
            ->route('animals.index')
            ->with('success', 'Animal deleted.');
    }
}
