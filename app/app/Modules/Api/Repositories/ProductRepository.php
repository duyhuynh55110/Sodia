<?php

namespace App\Modules\Api\Repositories;

use App\Models\Product;
use Base\Repositories\Eloquent\Repository;

class ProductRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return Product
     */
    public function model()
    {
        return Product::class;
    }

    /**
     * Get products list response paginate
     *
     * @param int $page
     * @param int $limit
     * @param array $
     * @param array $filter
     * @param int $orderBy
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getProducts(
        int $page,
        int $perPage,
        int $orderBy,
        array $columns = ['*'],
        array $filter = []
    ) {
        // https://laravel.com/docs/10.x/queries#full-text-where-clauses
        $query = $this->model->select($columns);

        // filter by search string
        $query->when(
            isset($filter['search']) && !empty($filter['search']),
            function ($q) use ($filter) {
                $q->whereFullText(['name_en', 'name_vi'], $filter['search']);
            }
        );

        // order by value
        $this->orderProductsListBy($query, $orderBy);

        // paginate
        return $query->paginate($perPage, [], 'page', $page);
    }

    /**
     * Get a product detail by slug_name
     *
     * @param string $slugName
     * @param array $columns
     * @return Product
     */
    public function getProductBySlugName(string $slugName, array $columns = ['*']) {
        return $this->model->select($columns)->where('slug_name', $slugName)->first();
    }

    /**
     * Order products list by value
     *
     * @param $query
     * @param int $orderBy
     * @return void
     */
    private function orderProductsListBy(&$query, int $orderBy) {
        // order by newest
        $query->when(in_array($orderBy, [ORDER_BY_TOP_SELLING, ORDER_BY_POPULAR, ORDER_BY_NEWEST]), fn($q) => $q->orderByDesc('id'));

        // order by price; low to height
        $query->when($orderBy == ORDER_BY_LOW_TO_HEIGHT, fn($q) => $q->orderBy('item_price'));

        // order by price; height to low
        $query->when($orderBy == ORDER_BY_HEIGHT_TO_LOW, fn($q) => $q->orderByDesc('item_price'));
    }
}
