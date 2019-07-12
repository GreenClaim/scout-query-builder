<?php

namespace Yource\ScoutQueryBuilder\Concerns;

use Yource\ScoutQueryBuilder\Sort;
use Illuminate\Support\Collection;
use Yource\ScoutQueryBuilder\ColumnNameSanitizer;
use Yource\ScoutQueryBuilder\Exceptions\InvalidSortQuery;

trait SortsQuery
{
    /** @var \Illuminate\Support\Collection */
    protected $defaultSorts;

    /** @var \Illuminate\Support\Collection */
    protected $allowedSorts;

    /** @var bool */
    protected $sortsWereParsed = false;

    /**
     * Per default, sorting is allowed on all columns if not specified otherwise.
     * We keep track of those default sorts to purge them if, at a later point in time, allowed sorts are specified.
     *
     * @var array
     */
    protected $generatedDefaultSorts = [];

    public function allowedSorts($sorts): self
    {
        $sorts = is_array($sorts) ? $sorts : func_get_args();

        if (! $this->request->sorts()) {
            return $this;
        }

        $this->allowedSorts = collect($sorts)->map(function ($sort) {
            if ($sort instanceof Sort) {
                return $sort;
            }

            return Sort::field(ltrim($sort, '-'));
        });

        $this->guardAgainstUnknownSorts();

        return $this;
    }

    /**
     * @param array|string|\Yource\ScoutQueryBuilder\Sort $sorts
     *
     * @return \Yource\ScoutQueryBuilder\QueryBuilder
     */
    public function defaultSort($sorts): self
    {
        return $this->defaultSorts(func_get_args());
    }

    /**
     * @param array|string|\Yource\ScoutQueryBuilder\Sort $sorts
     *
     * @return \Yource\ScoutQueryBuilder\QueryBuilder
     */
    public function defaultSorts($sorts): self
    {
        $sorts = is_array($sorts) ? $sorts : func_get_args();

        $this->defaultSorts = collect($sorts)->map(function ($sort) {
            if (is_string($sort)) {
                return Sort::field($sort);
            }

            return $sort;
        });

        return $this;
    }

    protected function parseSorts()
    {
        // Avoid repeated calls when used by e.g. 'paginate'
        if ($this->sortsWereParsed) {
            return;
        }

        $this->sortsWereParsed = true;

        if (!$this->allowedSorts instanceof Collection) {
            $this->addDefaultSorts();
            $this->allowRepeatedParse();
        } else { // Not sure what this is surposed to do...
            $this->purgeGeneratedDefaultSorts();
        }

        $sorts = $this->request->sorts();

        if ($sorts->isEmpty()) {
            optional($this->defaultSorts)->each(function (Sort $sort) {
                $sort->sort($this);
            });
        }

        $sorts->each(function (string $property) {
                $descending = $property[0] === '-';

                $key = ltrim($property, '-');

                $sort = $this->findSort($key);

                $sort->sort($this, $descending);
            });
    }

    protected function findSort(string $property): ?Sort
    {
        return $this->allowedSorts
            ->merge($this->defaultSorts)
            ->first(function (Sort $sort) use ($property) {
                return $sort->isForProperty($property);
            });
    }

    protected function addDefaultSorts()
    {
        $sanitizedSortColumns = $this->request->sorts()->map(function ($sort) {
            $sortColumn = ltrim($sort, '-');

            // This is the only place where query string parameters are passed as
            // sort columns directly. We need to sanitize these column names.
            return ColumnNameSanitizer::sanitize($sortColumn);
        });

        $this->allowedSorts = $sanitizedSortColumns->map(function ($column) {
            return Sort::field($column);
        });

        $this->generatedDefaultSorts = $sanitizedSortColumns->toArray();
    }

    protected function guardAgainstUnknownSorts()
    {
        $sortNames = $this->request->sorts()->map(function ($sort) {
            return ltrim($sort, '-');
        });

        $allowedSortNames = $this->allowedSorts->map->getProperty();

        $diff = $sortNames->diff($allowedSortNames);

        if ($diff->count()) {
            throw InvalidSortQuery::sortsNotAllowed($diff, $allowedSortNames);
        }
    }

    protected function allowRepeatedParse(): void
    {
        $this->sortsWereParsed = false;
    }

    /**
     * @todo not sure if this works as it is surposed to
     */
    protected function purgeGeneratedDefaultSorts(): void
    {
        $this->orders = collect($this->orders)
            ->reject(function ($order) {
                if (!isset($order['column'])) {
                    return false;
                }

                return in_array($order['column'], $this->generatedDefaultSorts);
            })->values()->all();
    }
}
