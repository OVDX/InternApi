<?php

namespace App\Http\Requests;

use App\Models\ContentBlock;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach ($this->all() as $key => $value) {
            if ($value === '' || $value === null) {
                $this->request->remove($key);
            }
        }
    }
    public function rules(): array
    {
        $newsId   = (int) $this->route('newsId');
        $maxOrder = ContentBlock::where('news_id', $newsId)->max('order') ?? 0;
        $type     = $this->input('type');

        $rules = [
            'type'  => 'sometimes|in:text,image,text_image_right,text_image_left',
            'order' => ['sometimes', 'integer', 'min:1', 'max:' . $maxOrder],
        ];

        if ($type === 'text') {
            $rules['text_content'] = 'required|string';
            $rules['image']        = 'prohibited';
        }

        if ($type === 'image') {
            $rules['image']        = 'required|image|max:2048';
            $rules['text_content'] = 'prohibited';
        }

        if (in_array($type, ['text_image_right', 'text_image_left'])) {
            $rules['text_content'] = 'required|string';
            $rules['image']        = 'required|image|max:2048';
        }

        return $rules;
    }

    public function messages(): array
    {
        $newsId   = (int) $this->route('newsId');
        $maxOrder = ContentBlock::where('news_id', $newsId)->max('order') ?? 0;

        return [
            'type.in'                 => 'Тип має бути: text, image, text_image_right, text_image_left',
            'order.integer'           => 'Порядковий номер має бути цілим числом',
            'order.min'               => 'Порядковий номер має бути більше 0',
            'order.max'               => 'Порядковий номер не може бути більше ' . $maxOrder,
            'text_content.required'   => 'Текст є обов\'язковим для цього типу блоку',
            'text_content.prohibited' => 'Текст не може бути заповнений для типу image',
            'image.required'          => 'Зображення є обов\'язковим для цього типу блоку',
            'image.prohibited'        => 'Зображення не може бути завантажене для типу text',
            'image.image'             => 'Файл повинен бути зображенням',
            'image.max'               => 'Розмір зображення не може перевищувати 2MB',
        ];
    }
}
