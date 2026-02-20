<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;  // Створити
use App\Http\Requests\UpdateCategoryRequest; // Створити
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct()
    {

    }

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

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create([
            'position' => $request->position,
            'is_active' => $request->boolean('is_active'),
        ]);

        foreach (['uk', 'en'] as $locale) {
            $data = $request->validated('translations')[$locale] ?? [];
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

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            'position' => $request->position,
            'is_active' => $request->boolean('is_active'),
        ]);

        foreach (['uk', 'en'] as $locale) {
            $data = $request->validated('translations')[$locale] ?? [];
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
