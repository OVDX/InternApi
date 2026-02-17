<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Database\Eloquent\Builder;

class PublicNewsQueryBuilder
{

    public function build(array $filters): Builder
    {
        $query = News::with(['contentBlocks', 'user'])
            ->published()
            ->orderBy('published_at', 'desc');

        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyAuthorFilter($query, $filters['author_id'] ?? null);
        $this->applyDateRangeFilter($query, $filters['date_from'] ?? null, $filters['date_to'] ?? null);

        return $query;
    }


    private function applySearch(Builder $query, ?string $search): void
    {
        if (filled($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }
    }


    private function applyAuthorFilter(Builder $query, ?int $authorId): void
    {
        if (filled($authorId)) {
            $query->where('user_id', $authorId);
        }
    }


    private function applyDateRangeFilter(Builder $query, ?string $dateFrom, ?string $dateTo): void
    {
        if (filled($dateFrom)) {
            $query->whereDate('published_at', '>=', $dateFrom);
        }

        if (filled($dateTo)) {
            $query->whereDate('published_at', '<=', $dateTo);
        }
    }
}
