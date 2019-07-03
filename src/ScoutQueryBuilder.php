<?php

namespace Yource\ScoutQueryBuilder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use ScoutElastic\Builders\FilterBuilder;
use Yource\ScoutQueryBuilder\ScoutQueryBuilderRequest;
use Yource\ScoutQueryBuilder\Concerns\AddsFieldsToQuery;
use Yource\ScoutQueryBuilder\Concerns\AddsIncludesToQuery;
use Yource\ScoutQueryBuilder\Concerns\SortsQuery;
use Yource\ScoutQueryBuilder\Concerns\FiltersQuery;

class ScoutQueryBuilder extends FilterBuilder
{
    use FiltersQuery,
        SortsQuery,
        AddsIncludesToQuery,
        AddsFieldsToQuery;
//        AppendsAttributesToResults;

    /** @var \Spatie\QueryBuilder\QueryBuilderRequest */
    protected $request;

    public function __construct(Model $model, ?Request $request = null)
    {
        parent::__construct($model);

//        $this->initializeFromBuilder($builder);

        $this->request = ScoutQueryBuilderRequest::fromRequest($request ?? request());
    }

    /**
     * Create a new QueryBuilder for a request and model.
     *
     * @param string|\Illuminate\Database\Query\Builder $baseQuery Model class or base query builder
     * @param \Illuminate\Http\Request                  $request
     *
     * @return \Spatie\QueryBuilder\QueryBuilder
     */
    public static function for($baseQuery, ?Request $request = null): self
    {
        if (is_string($baseQuery)) {
            $baseQuery = ($baseQuery)::query();
        }

        return new static($baseQuery, $request ?? request());
    }

//    public function getQuery()
//    {
//        $this->parseSorts();
//
//        if (! $this->allowedFields instanceof Collection) {
//            $this->addAllRequestedFields();
//        }
//
//        return parent::getQuery();
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function get($columns = ['*'])
//    {
//        $this->parseSorts();
//
//        if (! $this->allowedFields instanceof Collection) {
//            $this->addAllRequestedFields();
//        }
//
//        $results = parent::get($columns);
//
//        if ($this->request->appends()->isNotEmpty()) {
//            $results = $this->addAppendsToResults($results);
//        }
//
//        return $results;
//    }

    /**
     *
     * @todo what to do with $columns
     * @param null $perPage
     * @param array $columns
     * @param string $pageName
     * @param null $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->parseSorts();

        if (!$this->allowedFields instanceof Collection) {
            $this->addAllRequestedFields();
        }

        return parent::paginate($perPage, $pageName, $page);
    }
//
//    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
//    {
//        $this->parseSorts();
//
//        if (! $this->allowedFields instanceof Collection) {
//            $this->addAllRequestedFields();
//        }
//
//        return parent::simplePaginate($perPage, $columns, $pageName, $page);
//    }

//    /**
//     * Add the model, scopes, eager loaded relationships, local macro's and onDelete callback
//     * from the $builder to this query builder.
//     *
//     * @todo eager Load?
//     * @param Builder $builder
//     */
//    protected function initializeFromBuilder(Builder $builder)
//    {
////        $this->setModel($builder->getModel())
////            ->setEagerLoads($builder->getEagerLoads());
//
////        $builder->macro('getProtected', function (Builder $builder, string $property) {
////            return $builder->{$property};
////        });
////
////        $this->scopes = $builder->getProtected('scopes');
////
////        $this->localMacros = $builder->getProtected('localMacros');
////
////        $this->onDelete = $builder->getProtected('onDelete');
//    }
}
