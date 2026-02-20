<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'position' => 1,
                'is_active' => true,
                'translations' => [
                    'uk' => ['name' => 'Технології', 'description' => 'Новини про технології'],
                    'en' => ['name' => 'Technology', 'description' => 'Technology news'],
                ],
            ],
            [
                'position' => 2,
                'is_active' => true,
                'translations' => [
                    'uk' => ['name' => 'Політика', 'description' => 'Політичні новини'],
                    'en' => ['name' => 'Politics', 'description' => 'Political news'],
                ],
            ],
            [
                'position' => 3,
                'is_active' => true,
                'translations' => [
                    'uk' => ['name' => 'Спорт', 'description' => 'Спортивні новини'],
                    'en' => ['name' => 'Sports', 'description' => 'Sports news'],
                ],
            ],
            [
                'position' => 4,
                'is_active' => false,
                'translations' => [
                    'uk' => ['name' => 'Архів', 'description' => 'Старі новини'],
                    'en' => ['name' => 'Archive', 'description' => 'Old news'],
                ],
            ],
        ];

        foreach ($categories as $data) {
            $category = Category::create([
                'position' => $data['position'],
                'is_active' => $data['is_active'],
            ]);


            foreach ($data['translations'] as $locale => $translation) {
                $category->translateOrNew($locale)->name = $translation['name'];
                $category->translateOrNew($locale)->description = $translation['description'] ?? null;
            }
            $category->save();
        }
    }
}
