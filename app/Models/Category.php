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
        new OA\Property(property: 'description', type: 'string', example: 'Новини про технології', nullable: true),
        new OA\Property(property: 'position', type: 'integer', example: 1),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
    ],
    type: 'object'
)]
class Category extends Model
{
    use Translatable;

    public $translatedAttributes = ['name', 'description'];

    protected $fillable = ['position', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function news()
    {
        return $this->belongsToMany(News::class, 'category_news')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position', 'asc');
    }
}
