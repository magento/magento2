<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Search\Model\Query;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;

/**
 * Retrieve filtered product data based off given search criteria in a format that GraphQL can interpret.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var FieldSelection
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
     * @var ArgumentsProcessorInterface
     */
    private $argsSelection;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param ProductProvider $productDataProvider
     * @param FieldSelection $fieldSelection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param ArgumentsProcessorInterface|null $argsSelection
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        ProductProvider $productDataProvider,
        FieldSelection $fieldSelection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScopeConfigInterface $scopeConfig,
        ?ArgumentsProcessorInterface $argsSelection = null
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->productDataProvider = $productDataProvider;
        $this->fieldSelection = $fieldSelection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->argsSelection = $argsSelection ? : ObjectManager::getInstance()
            ->get(ArgumentsProcessorInterface::class);
    }

    /**
     * Filter catalog product data based off given search criteria
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
        $fields = $this->fieldSelection->getProductsFieldSelection($info);
        try {
            $searchCriteria = $this->buildSearchCriteria($info->fieldName, $args);
            $searchResults = $this->productDataProvider->getList($searchCriteria, $fields, false, false, $context);
        } catch (InputException $e) {
            return $this->createEmptyResult((int)$args['pageSize'], (int)$args['currentPage']);
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
     * @param string $fieldName
     * @param array $args
     * @return SearchCriteriaInterface
     * @throws GraphQlInputException
     * @throws InputException
     */
    private function buildSearchCriteria(string $fieldName, array $args): SearchCriteriaInterface
    {
        $processedArgs = $this->argsSelection->process($fieldName, $args);
        if (!empty($processedArgs['filter'])) {
            $processedArgs['filter'] = $this->formatFilters($processedArgs['filter']);
        }

        $criteria = $this->searchCriteriaBuilder->build($fieldName, $processedArgs);
        $criteria->setCurrentPage($processedArgs['currentPage']);
        $criteria->setPageSize($processedArgs['pageSize']);

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
                    $value = $value !== null ? str_replace('%', '', trim($value)) : '';
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
     * @param int $pageSize
     * @param int $currentPage
     * @return SearchResult
     */
    private function createEmptyResult(int $pageSize, int $currentPage): SearchResult
    {
        return $this->searchResultFactory->create(
            [
                'totalCount' => 0,
                'productsSearchResult' => [],
                'pageSize' => $pageSize,
                'currentPage' => $currentPage,
                'totalPages' => 0,
            ]
        );
    }
}
