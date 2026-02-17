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
            'is_published' => 'required|in:true,false,0,1',

            'content_blocks' => 'nullable|array',
            'content_blocks.*.type' => 'required|in:text,image,text_image_right,text_image_left',
            'content_blocks.*.order' => 'required|integer|min:1|distinct', // order має бути унікальним в межах масиву
            'content_blocks.*.text_content' => 'nullable|string',
            'content_blocks.*.image' => 'nullable|image|max:2048',
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
            'is_published.required' => 'Статус публікації є обов\'язковим',
            'is_published.boolean' => 'Статус публікації має бути true або false',

            'content_blocks.*.type.required' => 'Тип блоку є обов\'язковим',
            'content_blocks.*.type.in' => 'Тип має бути: text, image, text_image_right, text_image_left',
            'content_blocks.*.order.required' => 'Порядковий номер є обов\'язковим',
            'content_blocks.*.order.integer' => 'Порядковий номер має бути цілим числом',
            'content_blocks.*.order.min' => 'Порядковий номер має бути більше 0',
            'content_blocks.*.order.distinct' => 'Порядкові номери блоків мають бути унікальними',
            'content_blocks.*.image.image' => 'Файл повинен бути зображенням',
            'content_blocks.*.image.max' => 'Розмір зображення не може перевищувати 2MB',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $blocks = $this->input('content_blocks', []);

            foreach ($blocks as $index => $block) {
                $type = $block['type'] ?? null;

                // Перевірка: text блок повинен мати text_content
                if ($type === 'text' && empty($block['text_content'])) {
                    $validator->errors()->add(
                        "content_blocks.{$index}.text_content",
                        'Текст є обов\'язковим для типу text'
                    );
                }

                if ($type === 'image' && !$this->hasFile("content_blocks.{$index}.image")) {
                    $validator->errors()->add(
                        "content_blocks.{$index}.image",
                        'Зображення є обов\'язковим для типу image'
                    );
                }

                if (in_array($type, ['text_image_right', 'text_image_left'])) {
                    if (empty($block['text_content'])) {
                        $validator->errors()->add(
                            "content_blocks.{$index}.text_content",
                            'Текст є обов\'язковим для цього типу блоку'
                        );
                    }
                    if (!$this->hasFile("content_blocks.{$index}.image")) {
                        $validator->errors()->add(
                            "content_blocks.{$index}.image",
                            'Зображення є обов\'язковим для цього типу блоку'
                        );
                    }
                }
            }
        });
    }
}
