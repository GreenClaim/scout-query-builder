<?php

namespace Yource\ScoutQueryBuilder\Sorts;

use Laravel\Scout\Builder;

interface Sort
{
    public function __invoke(Builder $query, $descending, string $property): Builder;
}
