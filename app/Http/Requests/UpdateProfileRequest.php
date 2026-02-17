<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $this->user()->id,
            'bio' => 'nullable|string|max:1000',
            'password' => 'nullable|string|min:8|confirmed'
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Ім\'я не може перевищувати 255 символів',
            'email.email' => 'Невалідний формат email',
            'email.unique' => 'Цей email вже використовується',
            'bio.max' => 'Опис не може перевищувати 1000 символів',
            'password.min' => 'Пароль повинен містити мінімум 8 символів',
            'password.confirmed' => 'Паролі не співпадають'
        ];
    }
}
