<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Search\Api\SearchInterface;
use Magento\Search\Model\Search\PageSizeProvider;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;

/**
 * Full text search for catalog using given search criteria.
 */
class Search implements ProductQueryInterface
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
     * @var PageSizeProvider
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
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var FilterGroupBuilder */
    private $filterGroupBuilder;

    /**
     * @param SearchInterface $search
     * @param SearchResultFactory $searchResultFactory
     * @param PageSizeProvider $pageSize
     * @param SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param FieldSelection $fieldSelection
     * @param ProductSearch $productsProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        SearchInterface $search,
        SearchResultFactory $searchResultFactory,
        PageSizeProvider $pageSize,
        SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        FieldSelection $fieldSelection,
        ProductSearch $productsProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->search = $search;
        $this->searchResultFactory = $searchResultFactory;
        $this->pageSizeProvider = $pageSize;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->fieldSelection = $fieldSelection;
        $this->productsProvider = $productsProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * Return product search results using Search API
     *
     * @param array $args
     * @param ResolveInfo $info
     * @return SearchResult
     * @throws \Exception
     */
    public function getResult(
        array $args,
        ResolveInfo $info
    ): SearchResult {
        $queryFields = $this->fieldSelection->getProductsFieldSelection($info);
        $searchCriteria = $this->buildSearchCriteria($args, $info);

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

        $categoryGroup = null;
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == 'category_id') {
                    $categoryFilter = $this->filterBuilder
                        ->setField($filter->getField())
                        ->setValue($filter->getValue())
                        ->setConditionType($filter->getConditionType())
                        ->create();

                    $this->filterGroupBuilder->addFilter($categoryFilter);
                    $categoryGroup = $this->filterGroupBuilder->create();
                }
            }
        }
        //add root category or all root category children if category_id is not defined
        if ($categoryGroup) {
            $searchCriteriaCopy->setFilterGroups([$categoryGroup]);
        }

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

    /**
     * Build search criteria from query input args
     *
     * @param array $args
     * @param ResolveInfo $info
     * @return SearchCriteriaInterface
     */
    private function buildSearchCriteria(array $args, ResolveInfo $info): SearchCriteriaInterface
    {
        $productFields = (array)$info->getFieldSelection(1);
        $includeAggregations = isset($productFields['filters']) || isset($productFields['aggregations']);
        $searchCriteria = $this->searchCriteriaBuilder->build($args, $includeAggregations);

        return $searchCriteria;
    }
}
