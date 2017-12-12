<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\GraphQlCatalog\Model\Resolver\Products\SearchCriteria\Helper\Filter;
use Magento\Search\Api\SearchInterface;

/**
 * Full text search for catalog using given search criteria.
 */
class SearchDataProvider
{
    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var Filter
     */
    private $filterHelper;

    /**
     * @var FilterDataProvider
     */
    private $filterDataProvider;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @param SearchInterface $search
     * @param Filter $filterHelper
     * @param FilterDataProvider $filterDataProvider
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        SearchInterface $search,
        Filter $filterHelper,
        FilterDataProvider $filterDataProvider,
        SearchResultFactory $searchResultFactory
    ) {
        $this->search = $search;
        $this->filterHelper = $filterHelper;
        $this->filterDataProvider = $filterDataProvider;
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
        // Search starts pages from 0, whereas filtering starts at 1. GraphQL's query starts at 1, so it must be altered
        $searchCriteria->setCurrentPage($searchCriteria->getCurrentPage() - 1);
        $itemsResults = $this->search->search($searchCriteria);
        $ids = [];
        $searchIds = [];
        foreach ($itemsResults->getItems() as $item) {
            $ids[$item->getId()] = null;
            $searchIds[] = $item->getId();
        }
        $filter = $this->filterHelper->generate('entity_id', 'in', $searchIds);
        $searchCriteria->setCurrentPage($searchCriteria->getCurrentPage() + 1);
        $searchCriteria = $this->filterHelper->remove($searchCriteria, 'search_term');
        $searchCriteria = $this->filterHelper->add($searchCriteria, $filter);
        $searchResult = $this->filterDataProvider->getResult($searchCriteria);
        foreach ($searchResult->getProductsSearchResult() as $product) {
            $ids[$product['id']] = $product;
        }
        $products = array_filter($ids);

        return $this->searchResultFactory->create($searchResult->getTotalCount(), $products);
    }
}
