<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicNewsFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'author_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date|date_format:Y-m-d',
            'date_to' => 'nullable|date|date_format:Y-m-d|after_or_equal:date_from',
            'page' => 'nullable|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'author_id.exists' => 'Автора з таким ID не існує',
            'date_from.date_format' => 'Невалідний формат дати. Використовуйте YYYY-MM-DD',
            'date_to.date_format' => 'Невалідний формат дати. Використовуйте YYYY-MM-DD',
            'date_to.after_or_equal' => 'Дата кінця не може бути раніше дати початку',
            'page.min' => 'Номер сторінки повинен бути не менше 1'
        ];
    }
}
