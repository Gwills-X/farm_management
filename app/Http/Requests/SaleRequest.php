<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_date' => ['required', 'date', 'before_or_equal:today'],
            'buyer_name' => ['required', 'string', 'max:150'],
            'buyer_phone' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['nullable', Rule::in(['cash', 'bank_transfer', 'pos', 'other'])],
            'amount_paid' => ['nullable', 'numeric', 'min:0', 'max:99999999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.animal_id' => ['required', 'integer', 'exists:animals,id'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:99999999999.99'],
        ];
    }

    public function messages(): array
    {
        return ['items.required' => 'Add at least one animal to the sale.'];
    }
}
