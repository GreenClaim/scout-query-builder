<?php declare(strict_types=1);

namespace Yource\ScoutQueryBuilder\Filters;

use Laravel\Scout\Builder;

class FiltersWithOperators implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $values = is_array($value) ? $value : [$value];
        $operator = key($value);

        if (in_array($operator, ['in', 'nin'], true)) {
            $where = $this->getOperator($operator);
            return $query->$where($property, $values[$operator]);
        }

        foreach ($values as $operator => $value) {
            $query->where($property, $this->getOperator($operator), $value);
        }

        return $query;
    }

    protected function getOperator($operator): string
    {
        $operators = [
            'gt'  => '>',
            'gte' => '>=',
            'lt'  => '<',
            'lte' => '<=',
            'eq'  => '=',
            'neq' => '!=',
            'in'  => 'whereIn',
            'nin' => 'whereNotIn',
        ];

        return $operators[$operator] ?? '=';
    }
}
