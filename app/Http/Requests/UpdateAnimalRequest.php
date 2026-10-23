<?php

namespace App\Http\Requests;

use App\Models\Animal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * animal_type_id and animal_code are not listed, so they cannot be changed here.
     * Status transitions are checked in the controller against Animal::canTransitionTo().
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'sex' => ['required', Rule::in(['male', 'female', 'unknown'])],
            'breed' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'acquisition_type' => ['required', Rule::in(['purchased', 'born_on_farm', 'other'])],
            'acquisition_date' => ['required', 'date', 'before_or_equal:today'],
            'source' => ['nullable', 'string', 'max:150'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'status' => ['sometimes', Rule::in([
                Animal::STATUS_ACTIVE,
                Animal::STATUS_SOLD,
                Animal::STATUS_DECEASED,
                Animal::STATUS_ARCHIVED,
            ])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
