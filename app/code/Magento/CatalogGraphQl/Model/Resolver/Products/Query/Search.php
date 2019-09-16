<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
use Magento\Search\Api\SearchInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory;

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
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var \Magento\Search\Model\Search\PageSizeProvider
     */
    private $pageSizeProvider;

    /**
     * @var SearchCriteriaInterfaceFactory
     */
    private $searchCriteriaFactory;

    /**
     * @var FieldSelection
     */
    private $fieldSelection;

    /**
     * @var ProductSearch
     */
    private $productsProvider;

    /**
     * @param SearchInterface $search
     * @param SearchResultFactory $searchResultFactory
     * @param \Magento\Search\Model\Search\PageSizeProvider $pageSize
     * @param SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param FieldSelection $fieldSelection
     * @param ProductSearch $productsProvider
     */
    public function __construct(
        SearchInterface $search,
        SearchResultFactory $searchResultFactory,
        \Magento\Search\Model\Search\PageSizeProvider $pageSize,
        SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        FieldSelection $fieldSelection,
        ProductSearch $productsProvider
    ) {
        $this->search = $search;
        $this->searchResultFactory = $searchResultFactory;
        $this->pageSizeProvider = $pageSize;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->fieldSelection = $fieldSelection;
        $this->productsProvider = $productsProvider;
    }

    /**
     * Return results of full text catalog search of given term, and will return filtered results if filter is specified
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param ResolveInfo $info
     * @return SearchResult
     * @throws \Exception
     */
    public function getResult(
        SearchCriteriaInterface $searchCriteria,
        ResolveInfo $info
    ): SearchResult {
        $queryFields = $this->fieldSelection->getProductsFieldSelection($info);

        $realPageSize = $searchCriteria->getPageSize();
        $realCurrentPage = $searchCriteria->getCurrentPage();
        // Current page must be set to 0 and page size to max for search to grab all ID's as temporary workaround
        $pageSize = $this->pageSizeProvider->getMaxPageSize();
        $searchCriteria->setPageSize($pageSize);
        $searchCriteria->setCurrentPage(0);
        $itemsResults = $this->search->search($searchCriteria);

        //Create copy of search criteria without conditions (conditions will be applied by joining search result)
        $searchCriteriaCopy = $this->searchCriteriaFactory->create()
            ->setSortOrders($searchCriteria->getSortOrders())
            ->setPageSize($realPageSize)
            ->setCurrentPage($realCurrentPage);

        $searchResults = $this->productsProvider->getList($searchCriteriaCopy, $itemsResults, $queryFields);

        //possible division by 0
        if ($realPageSize) {
            $maxPages = (int)ceil($searchResults->getTotalCount() / $realPageSize);
        } else {
            $maxPages = 0;
        }
        $searchCriteria->setPageSize($realPageSize);
        $searchCriteria->setCurrentPage($realCurrentPage);

        $productArray = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($searchResults->getItems() as $product) {
            $productArray[$product->getId()] = $product->getData();
            $productArray[$product->getId()]['model'] = $product;
        }

        return $this->searchResultFactory->create(
            [
                'totalCount' => $searchResults->getTotalCount(),
                'productsSearchResult' => $productArray,
                'searchAggregation' => $itemsResults->getAggregations(),
                'pageSize' => $realPageSize,
                'currentPage' => $realCurrentPage,
                'totalPages' => $maxPages,
            ]
        );
    }
}
