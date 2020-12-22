<?php

namespace Yource\ScoutQueryBuilder\Filters;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Scout\Builder;

class FiltersExact implements Filter
{
    protected $relationConstraints = [];

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        if ($this->isNestedProperty($query, $property)) {
            return $this->withRelationConstraint($query, $value, $property);
        }

        if (is_array($value)) {
            return $query->whereIn($property, $value);
        }

        return $query->where($property, '=', $value);
    }

    protected function isNestedProperty(Builder $query, string $property): bool
    {
        if (!Str::contains($property, '.')) {
            return false;
        }

        if (in_array($property, $this->relationConstraints, true)) {
            return false;
        }

        $properties = explode('.', $property);
        $mappingType = $this->getMappingTypeForProperty($query, $properties[0]);

        return $mappingType === 'nested';
    }

    protected function getMappingTypeForProperty(Builder $query, string $property)
    {
        $mapping = $query->model->getMapping();
        $propertyMapping = $mapping['properties'][$property];

        return $propertyMapping['type'] ?? null;
    }

    protected function withRelationConstraint($query, $value, string $property): Builder
    {
        $values = is_array($value) ? $value : [$value];
        $operator = $this->getRelationOperator(key($values));
        $values = Arr::flatten($values);
        $properties = explode('.', $property, 2);

        $path = $properties[0];
        $field = $properties[1];

        if (is_array($values)) {
            $operator = $operator.'In';
            return $query->$operator($path, $field, $values);
        }

        return $query->$operator($path, $field, $values);
    }

    protected function getRelationOperator($operator): string
    {
        $operators = [
            'in'  => 'whereHas',
            'nin' => 'whereDoesntHave',
        ];

        return $operators[$operator] ?? 'whereHas';
    }
}
