<?php

namespace Yource\ScoutQueryBuilder\Filters;

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
        $properties = explode('.', $property);

        $path = $properties[0];
        $field = $properties[1];

        if (is_array($value)) {
            return $query->whereHasIn($path, $field, $value);
        }

        return $query->whereHas($path, $field, $value);
    }
}
