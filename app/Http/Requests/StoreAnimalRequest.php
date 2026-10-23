<?php

namespace App\Http\Requests;

use App\Models\Animal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'animal_type_id' => [
                'required',
                'integer',
                Rule::exists('animal_types', 'id')->where('is_active', true),
            ],
            'name' => ['nullable', 'string', 'max:100'],
            'sex' => ['required', Rule::in(['male', 'female', 'unknown'])],
            'breed' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'acquisition_type' => ['required', Rule::in(['purchased', 'born_on_farm', 'other'])],
            'acquisition_date' => ['required', 'date', 'before_or_equal:today'],
            'source' => ['nullable', 'string', 'max:150'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
