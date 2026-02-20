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
            'category_id' => 'nullable|integer|exists:categories,id',
            'category_ids' => 'nullable|array|max:10',
            'category_ids.*' => 'integer|exists:categories,id|min:1',
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
            'category_id.exists' => 'Категорія з таким ID не існує',
            'category_ids.array' => 'category_ids має бути масивом',
            'category_ids.max' => 'Максимум 10 категорій',
            'category_ids.*.exists' => 'Категорія з ID :value не існує',
            'category_ids.*.min' => 'ID категорії повинен бути більше 0',
            'author_id.exists' => 'Автора з таким ID не існує',
            'date_from.date_format' => 'Невалідний формат дати. Використовуйте YYYY-MM-DD',
            'date_to.date_format' => 'Невалідний формат дати. Використовуйте YYYY-MM-DD',
            'date_to.after_or_equal' => 'Дата кінця не може бути раніше дати початку',
            'page.min' => 'Номер сторінки повинен бути не менше 1'
        ];
    }
}
