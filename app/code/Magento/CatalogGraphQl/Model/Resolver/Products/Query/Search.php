<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search\QueryPopularity;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Search\Api\SearchInterface;

/**
 * Full text search for catalog using given search criteria.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var FieldSelection
     */
    private $fieldSelection;

    /**
     * @var ArgumentsProcessorInterface
     */
    private $argsSelection;

    /**
     * @var ProductSearch
     */
    private $productsProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Suggestions
     */
    private $suggestions;

    /**
     * @var QueryPopularity
     */
    private $queryPopularity;

    /**
     * @param SearchInterface $search
     * @param SearchResultFactory $searchResultFactory
     * @param FieldSelection $fieldSelection
     * @param ProductSearch $productsProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ArgumentsProcessorInterface $argsSelection
     * @param Suggestions $suggestions
     * @param QueryPopularity $queryPopularity
     */
    public function __construct(
        SearchInterface $search,
        SearchResultFactory $searchResultFactory,
        FieldSelection $fieldSelection,
        ProductSearch $productsProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ArgumentsProcessorInterface $argsSelection,
        Suggestions $suggestions,
        QueryPopularity $queryPopularity
    ) {
        $this->search = $search;
        $this->searchResultFactory = $searchResultFactory;
        $this->fieldSelection = $fieldSelection;
        $this->productsProvider = $productsProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->argsSelection = $argsSelection;
        $this->suggestions = $suggestions;
        $this->queryPopularity = $queryPopularity;
    }

    /**
     * Return product search results using Search API
     *
     * @param array $args
     * @param ResolveInfo $info
     * @param ContextInterface $context
     * @return SearchResult
     * @throws GraphQlInputException
     */
    public function getResult(
        array $args,
        ResolveInfo $info,
        ContextInterface $context
    ): SearchResult {
        $searchCriteria = $this->buildSearchCriteria($args, $info);
        $itemsResults = $this->search->search($searchCriteria);
        $searchResults = $this->productsProvider->getList(
            $searchCriteria,
            $itemsResults,
            $this->fieldSelection->getProductsFieldSelection($info),
            $context
        );

        $totalPages = $searchCriteria->getPageSize()
            ? ((int)ceil($searchResults->getTotalCount() / $searchCriteria->getPageSize()))
            : 0;

        // add query statistics data
        if (!empty($args['search'])) {
            $this->queryPopularity->execute($context, $args['search'], (int) $searchResults->getTotalCount());
        }

        $productArray = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($searchResults->getItems() as $product) {
            $productArray[$product->getId()] = $product->getData();
            $productArray[$product->getId()]['model'] = $product;
        }

        $suggestions = [];
        $totalCount = (int) $searchResults->getTotalCount();
        if ($totalCount === 0 && !empty($args['search'])) {
            $suggestions = $this->suggestions->execute($context, $args['search']);
        }

        return $this->searchResultFactory->create(
            [
                'totalCount' => $totalCount,
                'productsSearchResult' => $productArray,
                'searchAggregation' => $itemsResults->getAggregations(),
                'pageSize' => $args['pageSize'],
                'currentPage' => $args['currentPage'],
                'totalPages' => $totalPages,
                'suggestions' => $suggestions,
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
        $fieldName = $info->fieldName ?? "";
        $processedArgs = $this->argsSelection->process((string) $fieldName, $args);
        $searchCriteria = $this->searchCriteriaBuilder->build($processedArgs, $includeAggregations);

        return $searchCriteria;
    }
}
