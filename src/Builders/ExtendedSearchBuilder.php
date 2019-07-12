<?php

namespace Yource\ScoutQueryBuilder\Builders;

use ScoutElastic\Builders\SearchBuilder;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

class ExtendedSearchBuilder extends SearchBuilder
{
    /**
     * Add a where exact or field doesn't exists or is null condition.
     *
     * @see http://codedependant.net/2017/12/19/exact-match-or-null-queries-with-elasticsearch/
     *
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function whereOrNotExists($field, $value)
    {
        $args = func_get_args();

        if (count($args) == 3) {
            list($field, $operator, $value) = $args;
        } else {
            $operator = '=';
        }

        switch ($operator) {
            case '=':
                $wheres['must'][] = [
                    'term' => [
                        $field => $value,
                    ],
                ];
                break;

            case '>':
                $wheres['must'][] = [
                    'range' => [
                        $field => [
                            'gt' => $value,
                        ],
                    ],
                ];
                break;

            case '<':
                $wheres['must'][] = [
                    'range' => [
                        $field => [
                            'lt' => $value,
                        ],
                    ],
                ];
                break;

            case '>=':
                $wheres['must'][] = [
                    'range' => [
                        $field => [
                            'gte' => $value,
                        ],
                    ],
                ];
                break;

            case '<=':
                $wheres['must'][] = [
                    'range' => [
                        $field => [
                            'lte' => $value,
                        ],
                    ],
                ];
                break;

            case '!=':
            case '<>':
                $wheres['must_not'][] = [
                    'term' => [
                        $field => $value,
                    ],
                ];
                break;
        }

        $this->wheres['should'] = [
            [
                'bool' => $wheres,
            ],[
                'bool' => [
                    'must_not' => [
                        'exists' => [
                             'field' => $field,
                        ],
                    ],
                ],
            ]
        ];

        return $this;
    }
}