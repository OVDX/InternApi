<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|max:2048',
            'short_description' => 'sometimes|string'
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Назва не може перевищувати 255 символів',
            'image.image' => 'Файл повинен бути зображенням',
            'image.max' => 'Розмір зображення не може перевищувати 2MB',
        ];
    }
}
