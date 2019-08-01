<?php

namespace Yource\ScoutQueryBuilder\Builders;

use Closure;
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

    /**
     * Add a where nested object has value condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/nested.html
     *
     * @todo ripoff Eloquent whereHas to support closures, I want to be able to use queries on the nested object
     *
     * @param string $path the nested object where the value should excist
     * @param mixed $value
     * @return $this
     */
    public function whereHas($path, $field, $value)
    {
        $this->wheres['must']['nested'] = [
            'path' => $path,
            'query' => [
                'bool' => [
                    'must' => [
                        'match' => [
                            $path . '.' . $field => $value,
                        ]
                    ],
                ],
            ],
        ];

        return $this;
    }

    /**
     * Add a where nested object has one or more value condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/nested.html
     *
     * @todo ripoff Eloquent whereHas to support closures, I want to be able to use queries on the nested object
     *
     * @param string $path the nested object where the value should excist
     * @param mixed $value
     * @return $this
     */
    public function whereHasIn($path, $field, $value)
    {
        $this->wheres['must']['nested'] = [
            'path' => $path,
            'query' => [
                'bool' => [
                    'must' => [
                        'terms' => [
                            $path . '.' . $field => $value,
                        ]
                    ],
                ],
            ],
        ];

        return $this;
    }
}
