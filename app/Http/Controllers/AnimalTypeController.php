<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnimalTypeRequest;
use App\Models\AnimalType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnimalTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AnimalType::class);

        $search = trim((string) $request->query('search', ''));

        $types = AnimalType::query()
            ->withCount('animals')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('AnimalTypes/Index', [
            'types' => $types,
            'filters' => ['search' => $search],
            'canManage' => $request->user()->isAdmin(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', AnimalType::class);

        return Inertia::render('AnimalTypes/Form', [
            'type' => null,
        ]);
    }

    public function store(AnimalTypeRequest $request): RedirectResponse
    {
        $this->authorize('create', AnimalType::class);

        AnimalType::create($request->validated());

        return redirect()
            ->route('animal-types.index')
            ->with('success', 'Animal type created.');
    }

    public function edit(AnimalType $animalType): Response
    {
        $this->authorize('update', $animalType);

        $animalType->loadCount('animals');

        return Inertia::render('AnimalTypes/Form', [
            'type' => $animalType,
        ]);
    }

    public function update(AnimalTypeRequest $request, AnimalType $animalType): RedirectResponse
    {
        $this->authorize('update', $animalType);

        $data = $request->validated();

        // Codes already issued keep their prefix, but new codes must not change
        // mid-stream for a type that already has animals.
        if ($data['code_prefix'] !== $animalType->code_prefix && $animalType->animals()->exists()) {
            return back()
                ->withErrors(['code_prefix' => 'The code prefix cannot be changed once animals are registered under this type.'])
                ->withInput();
        }

        $animalType->update($data);

        return redirect()
            ->route('animal-types.index')
            ->with('success', 'Animal type updated.');
    }
}
