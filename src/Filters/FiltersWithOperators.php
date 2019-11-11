<?php declare(strict_types=1);

namespace Yource\ScoutQueryBuilder\Filters;

use Laravel\Scout\Builder;
use Illuminate\Support\Arr;

class FiltersWithOperators implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $values = is_array($value) ? $value : [$value];
        $operator = key($values);

        if (in_array($operator, ['in', 'nin'], true)) {
            $where = $this->getOperator($operator);
            return $query->$where($property, Arr::flatten($values));
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
