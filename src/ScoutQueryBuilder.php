<?php

namespace Yource\ScoutQueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Yource\ScoutQueryBuilder\Builders\ExtendedSearchBuilder;
use Yource\ScoutQueryBuilder\Concerns\AddsFieldsToQuery;
use Yource\ScoutQueryBuilder\Concerns\AddsIncludesToQuery;
use Yource\ScoutQueryBuilder\Concerns\AppendsAttributesToResults;
use Yource\ScoutQueryBuilder\Concerns\FiltersQuery;
use Yource\ScoutQueryBuilder\Concerns\SortsQuery;

class ScoutQueryBuilder extends ExtendedSearchBuilder
{
    use FiltersQuery,
        SortsQuery,
        AddsIncludesToQuery,
        AddsFieldsToQuery,
        AppendsAttributesToResults;

    /** @var \Yource\ScoutQueryBuilder\ScoutQueryBuilderRequest */
    protected $request;

    public function __construct(Builder $builder, $query, ?Request $request = null)
    {
        $softDelete = config('scout.soft_delete', false);

        $this->initializeFromBuilder($builder);

        parent::__construct($builder->getModel(), $query, null, $softDelete);

        $this->request = ScoutQueryBuilderRequest::fromRequest($request ?? request());
    }

    /**
     * Create a new QueryBuilder for a request and model.
     *
     * @param string|\Illuminate\Database\Query\Builder $baseQuery Model class or base query builder
     * @param \Illuminate\Http\Request                  $request
     *
     * @return \Yource\ScoutQueryBuilder\QueryBuilder
     */
    public static function for($baseQuery, $query = '*', ?Request $request = null): self
    {
        if (is_string($baseQuery)) {
            $baseQuery = ($baseQuery)::query();
        }

        return new static($baseQuery, $query, $request ?? request());
    }

    public function getQuery()
    {
        $this->parseSorts();

        if (!$this->allowedFields instanceof Collection) {
            $this->addAllRequestedFields();
        }

        return parent::getQuery();
    }

    /**
     * The scores computed by Elasticsearch
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-explain.html
     * @return array|mixed
     */
    public function getExplain()
    {
        $this->parseSorts();

        if (!$this->allowedFields instanceof Collection) {
            $this->addAllRequestedFields();
        }

        return $this->explain();
    }

    /**
     * Gives insights in the search requests
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-profile.html
     * @return array|mixed
     */
    public function getProfile()
    {
        $this->parseSorts();

        if (!$this->allowedFields instanceof Collection) {
            $this->addAllRequestedFields();
        }

        return $this->profile();
    }

    /**
     * {@inheritdoc}
     */
    public function get($columns = ['*'])
    {
        $this->parseSorts();

        if (!$this->allowedFields instanceof Collection) {
            $this->addAllRequestedFields();
        }

        $results = parent::get($columns);

        if ($this->request->appends()->isNotEmpty()) {
            $results = $this->addAppendsToResults($results);
        }

        return $results;
    }

    /**
     *
     * @todo what to do with $columns
     *
     * @param null $perPage
     * @param array $columns
     * @param string $pageName
     * @param null $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(
        $perPage = null,
        $pageName = 'page',
        $page = null
    ): LengthAwarePaginator {
        $this->parseSorts();

        if (!$this->allowedFields instanceof Collection) {
            $this->addAllRequestedFields();
        }

        return parent::paginate($perPage, $pageName, $page);
    }

    /**
     *
     * @todo what to do with $columns
     *
     * @param null $perPage
     * @param array $columns
     * @param string $pageName
     * @param null $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateRaw(
        $perPage = null,
        $pageName = 'page',
        $page = null
    ): LengthAwarePaginator {
        $this->parseSorts();

        if (!$this->allowedFields instanceof Collection) {
            $this->addAllRequestedFields();
        }

        return parent::paginateRaw($perPage, $pageName, $page);
    }

    /**
     *
     * @todo what to do with $columns
     *
     * @param null $perPage
     * @param array $columns
     * @param string $pageName
     * @param null $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function jsonPaginate(): LengthAwarePaginator
    {
        $this->parseSorts();

        $page = $this->request->page();

        if (!$this->allowedFields instanceof Collection) {
            $this->addAllRequestedFields();
        }

        return parent::paginate($page['size'], 'page', $page['number']);
    }

    /**
     * Add the global scopes from the $builder to this query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     */
    protected function initializeFromBuilder(Builder $builder): void
    {
        $builder->macro('getProtected', function (Builder $builder, string $property) {
            return $builder->{$property};
        });

        $scopes = $builder->getProtected('scopes');

        foreach ($scopes as $scope) {
            if ($scope instanceof ScoutScope) {
                $scope->apply($this, $builder->getModel());
            }
        }
    }
}
