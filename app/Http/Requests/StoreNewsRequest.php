<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'short_description' => 'required|string',
            'is_published' => 'required|in:0,1,true,false'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Назва новини є обов\'язковою',
            'title.max' => 'Назва не може перевищувати 255 символів',
            'image.image' => 'Файл повинен бути зображенням',
            'image.max' => 'Розмір зображення не може перевищувати 2MB',
            'short_description.required' => 'Короткий опис є обов\'язковим',
        ];
    }
}
