<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchCriteria\Helper\Filter as FilterHelper;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
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
    public function getResult(SearchCriteriaInterface $searchCriteria, ResolveInfo $info) : SearchResult
    {
        $realPageSize = $searchCriteria->getPageSize();
        $realCurrentPage = $searchCriteria->getCurrentPage();
        // Current page must be set to 0 and page size to max for search to grab all ID's as temporary workaround
        $searchCriteria->setPageSize(PHP_INT_MAX);
        $searchCriteria->setCurrentPage(0);
        $itemsResults = $this->search->search($searchCriteria);

        $ids = [];
        $searchIds = [];
        foreach ($itemsResults->getItems() as $item) {
            $ids[$item->getId()] = null;
            $searchIds[] = $item->getId();
        }

        $filter = $this->filterHelper->generate('entity_id', 'in', $searchIds);
        $searchCriteria = $this->filterHelper->remove($searchCriteria, 'search_term');
        $searchCriteria = $this->filterHelper->add($searchCriteria, $filter);
        $searchResult = $this->filterQuery->getResult($searchCriteria, $info);

        $searchCriteria->setPageSize($realPageSize);
        $searchCriteria->setCurrentPage($realCurrentPage);
        $paginatedProducts = $this->paginateList($searchResult->getProductsSearchResult(), $searchCriteria);

        $products = [];
        if (!isset($searchCriteria->getSortOrders()[0])) {
            foreach ($paginatedProducts as $product) {
                $productId = isset($product['entity_id']) ? $product['entity_id'] : $product['id'];
                if (in_array($productId, $searchIds)) {
                    $ids[$productId] = $product;
                }
            }
            $products = array_filter($ids);
        } else {
            foreach ($paginatedProducts as $product) {
                $productId = isset($product['entity_id']) ? $product['entity_id'] : $product['id'];
                if (in_array($productId, $searchIds)) {
                    $products[] = $product;
                }
            }
        }

        return $this->searchResultFactory->create($searchResult->getTotalCount(), $products);
    }

    /**
     * Paginate an array of Ids that get pulled back in search based off search criteria and total count.
     *
     * @param array $products
     * @param SearchCriteriaInterface $searchCriteria
     * @return int[]
     */
    private function paginateList(array $products, SearchCriteriaInterface $searchCriteria) : array
    {
        $length = $searchCriteria->getPageSize();
        // Search starts pages from 0
        $offset = $length * ($searchCriteria->getCurrentPage() - 1);
        return array_slice($products, $offset, $length);
    }
}
