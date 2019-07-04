<?php

return [

    /*
     * By default the package will use the `include`, `filter`, `sort` and `fields` query parameters.
     *
     * Here you can customize those names.
     */
    'parameters' => [
        'include' => 'include',

        'filter' => 'filter',

        'sort' => 'sort',

        'fields' => 'fields',

        'append' => 'append',

        'page' => 'page',
    ],

    /*
     * The maximum number of results that will be returned
     * when using the JSON API paginator.
     *
     * @see https://github.com/spatie/laravel-json-api-paginate
     */
    'max_results' => 300,

    /*
     * The default number of results that will be returned
     * when using the JSON API paginator.
     *
     * @see https://github.com/spatie/laravel-json-api-paginate
     */
    'default_size' => 30,

    /*
     * The key of the page[x] query string parameter for page number.
     */
    'number_parameter' => 'number',

    /*
     * The key of the page[x] query string parameter for page size.
     */
    'size_parameter' => 'size',

    /*
     * The name of the macro that is added to the Eloquent query builder.
     */
//    'method_name' => 'jsonPaginate',

    /*
     * Here you can override the base url to be used in the link items.
     */
//    'base_url' => null,

];
