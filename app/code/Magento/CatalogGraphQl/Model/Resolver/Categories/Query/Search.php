<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Categories\Query;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\CatalogGraphQl\Model\Resolver\Categories\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Categories\SearchResultFactory;
use Magento\Search\Api\SearchInterface;

/**
 * Full text search for catalog using given search criteria.
 */
class Search
{
    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @param SearchInterface $search
     */
    public function __construct(
        SearchInterface $search
    ) {
        $this->search = $search;
    }

    /**
     * Return results of full text catalog search of given term, and will return filtered results if filter is specified
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResult
     */
    public function getResult(SearchCriteriaInterface $searchCriteria)
    {
        $realPageSize = $searchCriteria->getPageSize();
        $realCurrentPage = $searchCriteria->getCurrentPage();
        // Current page must be set to 0 and page size to max for search to grab all ID's as temporary workaround
        // for MAGETWO-85611
        $searchCriteria->setPageSize(PHP_INT_MAX);
        $searchCriteria->setCurrentPage(0);
        $searchResult = $this->search->search($searchCriteria);
        $searchCriteria->setPageSize($realPageSize);
        $searchCriteria->setCurrentPage($realCurrentPage);
        return $this->searchResultFactory->create($searchResult->getTotalCount(), $searchResult->getItems());
    }
}
