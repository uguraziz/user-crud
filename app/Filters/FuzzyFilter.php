<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Spatie\QueryBuilder\Filters\Filter;

class FuzzyFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        if (empty($value)) {
            return $query;
        }

        $columns = Schema::getColumnListing($query->getModel()->getTable());

        return $query->where(function ($q) use ($columns, $value) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'ILIKE', "%{$value}%");
            }
        });
    }
}
