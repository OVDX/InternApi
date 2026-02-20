<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('categories.manage');
    }

    public function rules(): array
    {
        return [
            'position' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
            'translations' => 'required|array',
            'translations.uk.name' => 'required|string|max:255',
            'translations.uk.description' => 'nullable|string|max:1000',
            'translations.en.name' => 'required|string|max:255',
            'translations.en.description' => 'nullable|string|max:1000',
        ];
    }
}
