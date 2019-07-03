<?php

namespace Yource\ScoutQueryBuilder\Sorts;

use Laravel\Scout\Builder;

class SortsField implements Sort
{
    public function __invoke(Builder $query, $descending, string $property): Builder
    {
        return $query->orderBy($property, $descending ? 'desc' : 'asc');
    }
}
