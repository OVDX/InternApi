<?php

namespace App\Http\Requests;

use App\Models\Category;
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
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'short_description' => 'required|string',
            'is_published' => 'required|in:true,false,0,1',

            'category_ids' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (empty($value)) return;

                    $ids = is_string($value)
                        ? array_map('intval', explode(',', $value))
                        : (array) $value;

                    $validIds = array_filter($ids, fn($id) => $id > 0);

                    if (empty($validIds)) {
                        $fail('Виберіть хоча б одну категорію');
                        return;
                    }

                    if (count($validIds) > 10) {
                        $fail('Максимум 10 категорій');
                        return;
                    }

                    $invalidIds = array_diff($validIds, Category::pluck('id')->toArray());
                    if (!empty($invalidIds)) {
                        $fail('Категорії не існують: ' . implode(', ', $invalidIds));
                    }
                },
            ],

            'content_blocks' => 'nullable|array|max:50',
            'content_blocks.*.type' => 'required|in:text,image,text_image_right,text_image_left',
            'content_blocks.*.order' => 'required|integer|min:1|distinct',
            'content_blocks.*.text_content' => 'nullable|string',
            'content_blocks.*.image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Назва новини є обов\'язковою',
            'title.max' => 'Назва не може перевищувати 255 символів',
            'image.image' => 'Файл повинен бути зображенням',
            'image.mimes' => 'Підтримуються тільки форматі: jpeg, jpg, png',
            'image.max' => 'Розмір зображення не може перевищувати 2MB',
            'short_description.required' => 'Короткий опис є обов\'язковим',
            'is_published.required' => 'Статус публікації є обов\'язковим',
            'is_published.in' => 'Статус публікації має бути true або false',
            'category_ids' => 'Невірний формат ID категорій. Використовуйте "1,3" або [1,3]',


            'content_blocks.max' => 'Максимальна кількість блоків - 50',
            'content_blocks.*.type.required' => 'Тип блоку є обов\'язковим',
            'content_blocks.*.type.in' => 'Тип має бути: text, image, text_image_right, text_image_left',
            'content_blocks.*.order.required' => 'Порядковий номер є обов\'язковим',
            'content_blocks.*.order.integer' => 'Порядковий номер має бути цілим числом',
            'content_blocks.*.order.min' => 'Порядковий номер має бути більше 0',
            'content_blocks.*.order.distinct' => 'Порядкові номери блоків мають бути унікальними',
            'content_blocks.*.image.image' => 'Файл повинен бути зображенням',
            'content_blocks.*.image.mimes' => 'Підтримуються тільки форматі: jpeg, jpg, png',
            'content_blocks.*.image.max' => 'Розмір зображення не може перевищувати 2MB',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $categoryIds = $this->parseCategoryIds($this);
            if (!empty($categoryIds)) {
                $inactiveCount = Category::whereIn('id', $categoryIds)
                    ->where('is_active', false)
                    ->count();

                if ($inactiveCount > 0) {
                    $validator->errors()->add('category_ids', 'Є неактивні категорії');
                }
            }

            $blocks = $this->input('content_blocks', []);
            foreach ($blocks as $index => $block) {
                $type = $block['type'] ?? null;

                match($type) {
                    'text' => $this->validateTextBlock($validator, $index, $block),
                    'image' => $this->validateImageBlock($validator, $index, $block),
                    'text_image_right', 'text_image_left' =>
                    $this->validateTextImageBlock($validator, $index, $block),
                    default => null
                };
            }
        });
    }

    private function parseCategoryIds($request): array
    {
        if (!$request->filled('category_ids')) return [];

        $value = $request->category_ids;
        if (is_string($value)) {
            return array_filter(array_map('intval', explode(',', $value)), fn($id) => $id > 0);
        }
        if (is_array($value)) {
            return array_filter($value, fn($id) => is_numeric($id) && $id > 0);
        }
        return [];
    }

    private function validateTextBlock($validator, $index, $block)
    {
        if (empty(trim($block['text_content'] ?? ''))) {
            $validator->errors()->add("content_blocks.{$index}.text_content", 'Текст обов\'язковий');
        }
    }

    private function validateImageBlock($validator, $index, $block)
    {
        if (!$this->hasFile("content_blocks.{$index}.image")) {
            $validator->errors()->add("content_blocks.{$index}.image", 'Зображення обов\'язкове');
        }
    }

    private function validateTextImageBlock($validator, $index, $block)
    {
        if (empty(trim($block['text_content'] ?? ''))) {
            $validator->errors()->add("content_blocks.{$index}.text_content", 'Текст обов\'язковий');
        }
        if (!$this->hasFile("content_blocks.{$index}.image")) {
            $validator->errors()->add("content_blocks.{$index}.image", 'Зображення обов\'язкове');
        }
    }
}
