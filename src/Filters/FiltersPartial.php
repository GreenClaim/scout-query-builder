<?php

namespace Yource\ScoutQueryBuilder\Filters;

use Laravel\Scout\Builder;

class FiltersPartial extends FiltersExact
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        if ($this->isNestedProperty($query, $property)) {
            return $this->withRelationConstraint($query, $value, $property);
        }

        $value = str_replace(' ', '|', strtolower($value));

        return $query->whereRegexp($property, ".*{$value}.*");
    }
}
