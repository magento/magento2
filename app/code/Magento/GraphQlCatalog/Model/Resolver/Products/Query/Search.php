<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\Query;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\GraphQlCatalog\Model\Resolver\Products\SearchCriteria\Helper\Filter as FilterHelper;
use Magento\GraphQlCatalog\Model\Resolver\Products\SearchResult;
use Magento\GraphQlCatalog\Model\Resolver\Products\SearchResultFactory;
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
     * @var FilterHelper
     */
    private $filterHelper;

    /**
     * @var Filter
     */
    private $filterQuery;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @param SearchInterface $search
     * @param FilterHelper $filterHelper
     * @param Filter $filterQuery
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        SearchInterface $search,
        FilterHelper $filterHelper,
        Filter $filterQuery,
        SearchResultFactory $searchResultFactory
    ) {
        $this->search = $search;
        $this->filterHelper = $filterHelper;
        $this->filterQuery = $filterQuery;
        $this->searchResultFactory = $searchResultFactory;
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
        $searchCriteria->setPageSize(PHP_INT_MAX);
        // Search starts pages from 0, whereas filtering starts at 1. GraphQL's query starts at 1, so it must be altered
        $searchCriteria->setCurrentPage($realCurrentPage - 1);
        $itemsResults = $this->search->search($searchCriteria);

        $ids = [];
        $searchIds = [];
        foreach ($itemsResults->getItems() as $item) {
            $ids[$item->getId()] = null;
            $searchIds[] = $item->getId();
        }
        $searchCriteria->setPageSize($realPageSize);
        $paginatedIds = $this->paginateIdList($searchIds, $searchCriteria);
        $filter = $this->filterHelper->generate('entity_id', 'in', $paginatedIds);

        $searchCriteria->setCurrentPage($realCurrentPage);
        $searchCriteria = $this->filterHelper->remove($searchCriteria, 'search_term');
        $searchCriteria = $this->filterHelper->add($searchCriteria, $filter);
        $searchResult = $this->filterQuery->getResult($searchCriteria);
        foreach ($searchResult->getProductsSearchResult() as $product) {
            $ids[$product['id']] = $product;
        }
        $products = array_filter($ids);

        return $this->searchResultFactory->create($itemsResults->getTotalCount(), $products);
    }

    /**
     * Paginates array of Ids pulled back in search based off search criteria and total count.
     *
     * This function and its usages should be removed after MAGETWO-85611 is resolved.
     *
     * @param int[] $ids
     * @param SearchCriteriaInterface $searchCriteria
     * @return int[]
     */
    private function paginateIdList(array $ids, SearchCriteriaInterface $searchCriteria)
    {
        $length = $searchCriteria->getPageSize();
        $offset = $length * $searchCriteria->getCurrentPage();
        return array_slice($ids, $offset, $length);
    }
}
