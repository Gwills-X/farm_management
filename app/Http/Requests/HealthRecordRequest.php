<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HealthRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'observation_date' => ['required', 'date', 'before_or_equal:today'],
            'observed_condition' => ['required', 'string', 'max:2000'],
            'health_status' => ['required', Rule::in(['healthy', 'monitoring', 'sick', 'recovered'])],
            'treatment' => ['nullable', 'string', 'max:2000'],
            'medication' => ['nullable', 'string', 'max:1000'],
            'veterinary_professional' => ['nullable', 'string', 'max:150'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:observation_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
