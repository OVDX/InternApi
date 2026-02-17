<?php

namespace App\Http\Requests;

use App\Models\ContentBlock;
use Illuminate\Foundation\Http\FormRequest;

class StoreNewsBlockRequest extends FormRequest
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
            'is_published' => 'required|boolean',

            // Валідація масиву блоків
            'content_blocks' => 'nullable|array',
            'content_blocks.*.type' => 'required|in:text,image,text_image_right,text_image_left',
            'content_blocks.*.order' => 'required|integer|min:1',
            'content_blocks.*.text_content' => 'required_if:content_blocks.*.type,text,text_image_right,text_image_left|nullable|string',
            'content_blocks.*.image' => 'required_if:content_blocks.*.type,image,text_image_right,text_image_left|nullable|image|max:2048',
        ];
    }


    public function messages(): array
    {
        $newsId   = (int) $this->route('newsId');
        $maxOrder = ContentBlock::where('news_id', $newsId)->max('order') ?? 0;

        return [
            'type.required'           => 'Тип блоку є обов\'язковим',
            'type.in'                 => 'Тип має бути: text, image, text_image_right, text_image_left',
            'order.integer'           => 'Порядковий номер має бути цілим числом',
            'order.min'               => 'Порядковий номер має бути більше 0',
            'order.max'               => 'Порядковий номер не може бути більше ' . ($maxOrder + 1),
            'text_content.required'   => 'Текст є обов\'язковим для цього типу блоку',
            'text_content.prohibited' => 'Текст не може бути заповнений для типу image',
            'image.required'          => 'Зображення є обов\'язковим для цього типу блоку',
            'image.prohibited'        => 'Зображення не може бути завантажене для типу text',
            'image.image'             => 'Файл повинен бути зображенням',
            'image.max'               => 'Розмір зображення не може перевищувати 2MB',
        ];
    }
}
