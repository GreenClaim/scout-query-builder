<?php declare(strict_types=1);

namespace Yource\ScoutQueryBuilder\Filters;

use Laravel\Scout\Builder;

class FiltersWithOperators implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $value = is_array($value) ? $value : [$value];

        foreach ($value as $operator => $value) {
            $query->where($property, $this->getOperator($operator), $value);
        }

        return $query;
    }

    protected function getOperator($operator): string
    {
        $operators = [
            'gt'  => '>',
            'gte' => '>=',
            'lt'  => '>',
            'lte' => '<=',
            'eq'  => '=',
            'neq' => '!=',
        ];

        return $operators[$operator] ?? '=';
    }
}
