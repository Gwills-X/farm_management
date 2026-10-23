<?php

namespace App\Services;

use App\Models\AnimalType;

class AnimalCodeGenerator
{
    /**
     * Returns the next animal code for the given type, e.g. GOA-00012.
     *
     * Must be called inside a DB transaction. The row lock on animal_types
     * serialises concurrent registrations for the same type, so two requests
     * can never receive the same sequence number.
     */
    public function nextCodeFor(AnimalType $type): string
    {
        $locked = AnimalType::query()
            ->whereKey($type->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        $locked->code_sequence = $locked->code_sequence + 1;
        $locked->save();

        return sprintf('%s-%05d', $locked->code_prefix, $locked->code_sequence);
    }
}
