<?php
// app/Http/Controllers/Admin/CategoryController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{

    public function index(): View
    {
        $categories = Category::with('translations')
            ->ordered()
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }


    public function create(): View
    {
        $locales = ['uk', 'en'];

        return view('admin.categories.create', compact('locales'));
    }


    public function store(Request $request): RedirectResponse
    {
        $locales = ['uk', 'en'];

        $validated = $request->validate([
            'position' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
            'translations' => 'required|array',
            'translations.uk.name' => 'required|string|max:255',
            'translations.uk.description' => 'nullable|string|max:1000',
            'translations.en.name' => 'required|string|max:255',
            'translations.en.description' => 'nullable|string|max:1000',
        ]);

        $category = Category::create([
            'position' => $validated['position'],
            'is_active' => $request->boolean('is_active'),
        ]);

        foreach ($locales as $locale) {
            $data = $validated['translations'][$locale] ?? [];
            $category->translateOrNew($locale)->name = $data['name'] ?? null;
            $category->translateOrNew($locale)->description = $data['description'] ?? null;
        }

        $category->save();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Категорію створено');
    }


    public function edit(Category $category): View
    {
        $category->load('translations');
        $locales = ['uk', 'en'];

        return view('admin.categories.edit', compact('category', 'locales'));
    }


    public function update(Request $request, Category $category): RedirectResponse
    {
        $locales = ['uk', 'en'];

        $validated = $request->validate([
            'position' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
            'translations' => 'required|array',
            'translations.uk.name' => 'required|string|max:255',
            'translations.uk.description' => 'nullable|string|max:1000',
            'translations.en.name' => 'required|string|max:255',
            'translations.en.description' => 'nullable|string|max:1000',
        ]);

        $category->update([
            'position' => $validated['position'],
            'is_active' => $request->boolean('is_active'),
        ]);

        foreach ($locales as $locale) {
            $data = $validated['translations'][$locale] ?? [];
            $category->translateOrNew($locale)->name = $data['name'] ?? null;
            $category->translateOrNew($locale)->description = $data['description'] ?? null;
        }

        $category->save();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Категорію оновлено');
    }


    public function destroy(Category $category): RedirectResponse
    {
        if ($category->news()->exists()) {
            return back()->with('error', 'Неможливо видалити категорію, до неї прив\'язані новини');
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Категорію видалено');
    }
}
