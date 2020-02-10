<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
use Magento\Search\Model\Query;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Retrieve filtered product data based off given search criteria in a format that GraphQL can interpret.
 */
class Filter implements ProductQueryInterface
{
    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var ProductProvider
     */
    private $productDataProvider;

    /**
     * @var LayerResolver
     */
    private $layerResolver;

    /**
     * FieldSelection
     */
    private $fieldSelection;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param ProductProvider $productDataProvider
     * @param LayerResolver $layerResolver
     * @param FieldSelection $fieldSelection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        ProductProvider $productDataProvider,
        LayerResolver $layerResolver,
        FieldSelection $fieldSelection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->productDataProvider = $productDataProvider;
        $this->layerResolver = $layerResolver;
        $this->fieldSelection = $fieldSelection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Filter catalog product data based off given search criteria
     *
     * @param array $args
     * @param ResolveInfo $info
     * @return SearchResult
     */
    public function getResult(
        array $args,
        ResolveInfo $info
    ): SearchResult {
        $fields = $this->fieldSelection->getProductsFieldSelection($info);
        try {
            $searchCriteria = $this->buildSearchCriteria($args, $info);
            $searchResults = $this->productDataProvider->getList($searchCriteria, $fields);
        } catch (InputException $e) {
            return $this->createEmptyResult($args);
        }

        $productArray = [];
        /** @var Product $product */
        foreach ($searchResults->getItems() as $product) {
            $productArray[$product->getId()] = $product->getData();
            $productArray[$product->getId()]['model'] = $product;
        }

        //possible division by 0
        if ($searchCriteria->getPageSize()) {
            $maxPages = (int)ceil($searchResults->getTotalCount() / $searchCriteria->getPageSize());
        } else {
            $maxPages = 0;
        }

        return $this->searchResultFactory->create(
            [
                'totalCount' => $searchResults->getTotalCount(),
                'productsSearchResult' => $productArray,
                'pageSize' => $searchCriteria->getPageSize(),
                'currentPage' => $searchCriteria->getCurrentPage(),
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
        if (!empty($args['filter'])) {
            $args['filter'] = $this->formatFilters($args['filter']);
        }

        $criteria = $this->searchCriteriaBuilder->build($info->fieldName, $args);
        $criteria->setCurrentPage($args['currentPage']);
        $criteria->setPageSize($args['pageSize']);

        return $criteria;
    }

    /**
     * Reformat filters
     *
     * @param array $filters
     * @return array
     * @throws InputException
     */
    private function formatFilters(array $filters): array
    {
        $formattedFilters = [];
        $minimumQueryLength = $this->scopeConfig->getValue(
            Query::XML_PATH_MIN_QUERY_LENGTH,
            ScopeInterface::SCOPE_STORE
        );

        foreach ($filters as $field => $filter) {
            foreach ($filter as $condition => $value) {
                if ($condition === 'match') {
                    // reformat 'match' filter so MySQL filtering behaves like SearchAPI filtering
                    $condition = 'like';
                    $value = str_replace('%', '', trim($value));
                    if (strlen($value) < $minimumQueryLength) {
                        throw new InputException(__('Invalid match filter'));
                    }
                    $value = '%' . preg_replace('/ +/', '%', $value) . '%';
                }
                $formattedFilters[$field] = [$condition => $value];
            }
        }

        return $formattedFilters;
    }

    /**
     * Return and empty SearchResult object
     *
     * Used for handling exceptions gracefully
     *
     * @param array $args
     * @return SearchResult
     */
    private function createEmptyResult(array $args): SearchResult
    {
        return $this->searchResultFactory->create(
            [
                'totalCount' => 0,
                'productsSearchResult' => [],
                'pageSize' => $args['pageSize'],
                'currentPage' => $args['currentPage'],
                'totalPages' => 0,
            ]
        );
    }
}
