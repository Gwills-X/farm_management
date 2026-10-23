<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999999.99'],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'payment_method' => ['nullable', Rule::in(['cash', 'bank_transfer', 'pos', 'other'])],
        ];
    }
}
