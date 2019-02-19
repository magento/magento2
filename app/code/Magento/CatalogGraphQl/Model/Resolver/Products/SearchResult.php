<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Container for a product search holding the item result and the array in the GraphQL-readable product type format.
 */
class SearchResult
{
    /**
     * @var SearchResultsInterface
     */
    private $totalCount;

    /**
     * @var array
     */
    private $productsSearchResult;

    /**
     * @param int $totalCount
     * @param array $productsSearchResult
     */
    public function __construct(int $totalCount, array $productsSearchResult)
    {
        $this->totalCount = $totalCount;
        $this->productsSearchResult = $productsSearchResult;
    }

    /**
     * Return total count of search and filtered result
     *
     * @return int
     */
    public function getTotalCount() : int
    {
        return $this->totalCount;
    }

    /**
     * Retrieve an array in the format of GraphQL-readable type containing product data.
     *
     * @return array
     */
    public function getProductsSearchResult() : array
    {
        return $this->productsSearchResult;
    }
}
