<?php

namespace Yource\ScoutQueryBuilder\Filters;

use Laravel\Scout\Builder;

interface Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder;
}
