<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products;

use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Container for a product search holding the item result and the array in the GraphQL-readable product type format.
 */
class SearchResult
{
    /**
     * @var SearchResultsInterface
     */
    private $productSearchResult;

    /**
     * @var array
     */
    private $productSearchArray;

    /**
     * @param SearchResultsInterface $productSearchResult
     * @param array $productSearchArray
     */
    public function __construct(SearchResultsInterface $productSearchResult, array $productSearchArray)
    {
        $this->productSearchResult = $productSearchResult;
        $this->productSearchArray = $productSearchArray;
    }

    /**
     * Return the Search Results Interface containing metadata about the search, including total count.
     *
     * @return SearchResultsInterface
     */
    public function getObject()
    {
        return $this->productSearchResult;
    }

    /**
     * Retrieve an array in the format of GraphQL-readable type containing product data.
     *
     * @return array
     */
    public function getArray()
    {
        return $this->productSearchArray;
    }
}
