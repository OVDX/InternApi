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
            'short_description' => 'sometimes|string',

            'content_blocks' => 'sometimes|array',
            'content_blocks.*.id' => 'sometimes|integer|exists:content_blocks,id',
            'content_blocks.*.type' => 'required|in:text,image,text_image_right,text_image_left',
            'content_blocks.*.order' => 'required|integer|min:1|distinct',
            'content_blocks.*.text_content' => 'nullable|string',
            'content_blocks.*.image' => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Назва не може перевищувати 255 символів',
            'image.image' => 'Файл повинен бути зображенням',
            'image.max' => 'Розмір зображення не може перевищувати 2MB',

            'content_blocks.*.id.exists' => 'Блок з таким ID не існує',
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
                $blockId = $block['id'] ?? null;

                if ($type === 'text' && empty($block['text_content'])) {
                    $validator->errors()->add(
                        "content_blocks.{$index}.text_content",
                        'Текст є обов\'язковим для типу text'
                    );
                }

                if ($type === 'image' && !$blockId && !$this->hasFile("content_blocks.{$index}.image")) {
                    $validator->errors()->add(
                        "content_blocks.{$index}.image",
                        'Зображення є обов\'язковим для нового блоку типу image'
                    );
                }

                if (in_array($type, ['text_image_right', 'text_image_left'])) {
                    if (empty($block['text_content'])) {
                        $validator->errors()->add(
                            "content_blocks.{$index}.text_content",
                            'Текст є обов\'язковим для цього типу блоку'
                        );
                    }
                    if (!$blockId && !$this->hasFile("content_blocks.{$index}.image")) {
                        $validator->errors()->add(
                            "content_blocks.{$index}.image",
                            'Зображення є обов\'язковим для нового блоку цього типу'
                        );
                    }
                }
            }
        });
    }
}
