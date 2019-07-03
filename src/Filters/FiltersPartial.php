<?php

namespace Yource\ScoutQueryBuilder\Filters;

use Laravel\Scout\Builder;

class FiltersPartial extends FiltersExact
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        if ($this->isRelationProperty($query, $property)) {
            return $this->withRelationConstraint($query, $value, $property);
        }

        $wrappedProperty = $query->getQuery()->getGrammar()->wrap($property);

        $sql = "LOWER({$wrappedProperty}) LIKE ?";

        if (is_array($value)) {
            return $query->where(function (Builder $query) use ($value, $sql) {
                foreach ($value as $partialValue) {
                    $partialValue = mb_strtolower($partialValue, 'UTF8');

                    $query->orWhereRaw($sql, ["%{$partialValue}%"]);
                }
            });
        }

        $value = mb_strtolower($value, 'UTF8');

        return $query->whereRaw($sql, ["%{$value}%"]);
    }
}
