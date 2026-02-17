<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Файл аватара є обов\'язковим',
            'avatar.image' => 'Файл повинен бути зображенням',
            'avatar.mimes' => 'Дозволені формати: jpg, jpeg, png',
            'avatar.max' => 'Розмір файлу не може перевищувати 2MB'
        ];
    }
}
