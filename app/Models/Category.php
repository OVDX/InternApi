<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Category',
    title: 'Category',
    description: 'Категорія новин',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Технології'),
        new OA\Property(property: 'description', type: 'string', example: 'Новини про технології'),
        new OA\Property(property: 'position', type: 'integer', example: 1),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class Category extends Model
{
    use Translatable;

    public $translatedAttributes = ['name', 'description'];

    protected $fillable = ['position', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Зв'язок з новинами (Many-to-Many)
     */
    public function news()
    {
        return $this->belongsToMany(News::class, 'category_news')
            ->withTimestamps();
    }

    /**
     * Scope для отримання тільки активних категорій
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для сортування за позицією
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position', 'asc');
    }
}
