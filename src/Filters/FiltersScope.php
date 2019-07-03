<?php

namespace Yource\ScoutQueryBuilder\Filters;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Scout\Builder;

class FiltersScope implements Filter
{
    public function __invoke(Builder $query, $values, string $property): Builder
    {
        $scope = Str::camel($property);
        $values = Arr::wrap($values);

        return $query->$scope(...$values);
    }
}
