<?php

namespace App\Http\Requests;

use App\Models\AnimalType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnimalTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorisation is enforced by the role:admin route middleware.
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->code_prefix)) {
            $this->merge(['code_prefix' => strtoupper(trim($this->code_prefix))]);
        }
    }

    public function rules(): array
    {
        // Route parameter is {animalType} (set explicitly in routes/web.php).
        /** @var AnimalType|null $type */
        $type = $this->route('animalType');

        $nameRule = Rule::unique('animal_types', 'name');
        $prefixRule = Rule::unique('animal_types', 'code_prefix');

        if ($type) {
            $nameRule->ignore($type->id);
            $prefixRule->ignore($type->id);
        }

        // Prefix is optional on create (derived from the name) and required on edit.
        $prefixRequired = $this->isMethod('post') ? 'nullable' : 'required';

        return [
            'name' => ['required', 'string', 'max:100', $nameRule],
            'code_prefix' => [$prefixRequired, 'regex:/^[A-Z]{2,6}$/', $prefixRule],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code_prefix.regex' => 'The code prefix must be 2 to 6 letters, with no numbers or spaces.',
        ];
    }
}
