<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products;

use Magento\Framework\Api\Search\AggregationInterface;

/**
 * Container for a product search holding the item result and the array in the GraphQL-readable product type format.
 */
class SearchResult
{
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Return total count of search and filtered result
     *
     * @return int
     */
    public function getTotalCount() : int
    {
        return $this->data['totalCount'] ?? 0;
    }

    /**
     * Retrieve an array in the format of GraphQL-readable type containing product data.
     *
     * @return array
     */
    public function getProductsSearchResult() : array
    {
        return $this->data['productsSearchResult'] ?? [];
    }

    /**
     * Retrieve aggregated search results
     *
     * @return AggregationInterface|null
     */
    public function getSearchAggregation(): ?AggregationInterface
    {
        return $this->data['searchAggregation'] ?? null;
    }

    /**
     * Retrieve the page size for the search
     *
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->data['pageSize'] ?? 0;
    }

    /**
     * Retrieve the current page for the search
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->data['currentPage'] ?? 0;
    }

    /**
     * Retrieve total pages for the search
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->data['totalPages'] ?? 0;
    }
}
