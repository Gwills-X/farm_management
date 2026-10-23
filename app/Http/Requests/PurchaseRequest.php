<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_date' => ['required', 'date', 'before_or_equal:today'],
            'seller_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.animal_type_id' => [
                'required',
                'integer',
                Rule::exists('animal_types', 'id')->where('is_active', true),
            ],
            'items.*.name' => ['nullable', 'string', 'max:100'],
            'items.*.sex' => ['required', Rule::in(['male', 'female', 'unknown'])],
            'items.*.breed' => ['nullable', 'string', 'max:100'],
            'items.*.date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'items.*.weight_kg' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:99999999999.99'],
        ];
    }

    public function messages(): array
    {
        return ['items.required' => 'Add at least one animal to the purchase.'];
    }
}
