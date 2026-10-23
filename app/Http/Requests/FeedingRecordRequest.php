<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedingRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fed_at' => ['required', 'date', 'before_or_equal:now'],
            'feed_type' => ['required', 'string', 'max:150'],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'unit' => ['required', Rule::in(['kg', 'g', 'litre', 'bag', 'other'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
