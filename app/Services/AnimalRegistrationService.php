<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\AnimalType;
use DomainException;
use Illuminate\Support\Facades\DB;

class AnimalRegistrationService
{
    public function __construct(private AnimalCodeGenerator $codes)
    {
    }

    /**
     * Registers a new individual animal and generates its code on the backend.
     *
     * @param  array<string, mixed>  $data  Validated attributes. Must NOT contain animal_code.
     */
    public function register(array $data, ?int $userId = null): Animal
    {
        return DB::transaction(function () use ($data, $userId) {
            $type = AnimalType::query()->findOrFail($data['animal_type_id']);

            if (! $type->is_active) {
                throw new DomainException('This animal type is inactive and cannot receive new animals.');
            }

            $animal = new Animal($data);
            $animal->animal_type_id = $type->id;
            $animal->status = Animal::STATUS_ACTIVE;
            $animal->created_by = $userId;
            // Set directly because animal_code is intentionally not mass-assignable.
            $animal->animal_code = $this->codes->nextCodeFor($type);
            $animal->save();

            return $animal;
        });
    }
}
