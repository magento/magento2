<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolverFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplierPool;
use Magento\Framework\Search\Request\Config as SearchConfig;

/**
 * Build search criteria
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchCriteriaBuilder
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param Visibility $visibility
     * @param SortOrderBuilder $sortOrderBuilder
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchConfig $searchConfig
     * @param RequestDataBuilder $localData
     * @param SearchCriteriaResolverFactory $criteriaResolverFactory
     * @param ArgumentApplierPool $argumentApplierPool
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly FilterBuilder $filterBuilder,
        private readonly FilterGroupBuilder $filterGroupBuilder,
        private readonly Visibility $visibility,
        private readonly SortOrderBuilder $sortOrderBuilder,
        private readonly ProductAttributeRepositoryInterface $productAttributeRepository,
        private readonly SearchConfig $searchConfig,
        private readonly RequestDataBuilder $localData,
        private readonly SearchCriteriaResolverFactory $criteriaResolverFactory,
        private readonly ArgumentApplierPool $argumentApplierPool,
    ) {
    }

    /**
     * Build search criteria
     *
     * @param array $args
     * @param bool $includeAggregation
     * @return SearchCriteriaInterface
     * @throws LocalizedException
     */
    public function build(array $args, bool $includeAggregation): SearchCriteriaInterface
    {
        $isSearch = isset($args['search']);
        $requestName = $includeAggregation ? 'graphql_product_search_with_aggregation' : 'graphql_product_search';

        if (isset($args['filter'])) {
            $partialMatchFilters = $this->getPartialMatchFilters($args);
            if (count($partialMatchFilters)) {
                $this->updateMatchTypeRequestConfig($requestName, $partialMatchFilters);
            }
            $args = $this->removeMatchTypeFromArguments($args);
        }

        $searchCriteria = $this->criteriaResolverFactory->create(
            [
                'searchRequestName' => $requestName,
                'currentPage' => $args['currentPage'],
                'size' => $args['pageSize'],
                'orders' => null,
            ]
        )->resolve();
        foreach ($args as $argumentName => $argument) {
            if ($this->argumentApplierPool->hasApplier($argumentName)) {
                $argumentApplier = $this->argumentApplierPool->getApplier($argumentName);
                $argumentApplier->applyArgument($searchCriteria, 'products', $argumentName, $argument);
            }
        }
        $this->updateRangeFilters($searchCriteria);
        $this->preparePriceAggregation($searchCriteria, $includeAggregation);
        if ($isSearch) {
            $this->addFilter($searchCriteria, 'search_term', $args['search']);
        }
        if (!$searchCriteria->getSortOrders()) {
            $this->addDefaultSortOrder($searchCriteria, $args, $isSearch);
        }
        $this->addEntityIdSort($searchCriteria);
        $this->addVisibilityFilter($searchCriteria, $isSearch, !empty($args['filter']['category_id']));

        return $searchCriteria;
    }

    /**
     * Update dynamically the search match type based on requested params
     *
     * @param string $requestName
     * @param array $partialMatchFilters
     * @return void
     */
    private function updateMatchTypeRequestConfig(string $requestName, array $partialMatchFilters): void
    {
        $data = $this->searchConfig->get($requestName);
        foreach ($data['queries'] as $queryName => $query) {
            foreach ($query['match'] ?? [] as $index => $matchItem) {
                if (in_array($matchItem['field'] ?? null, $partialMatchFilters, true)) {
                    $data['queries'][$queryName]['match'][$index]['matchCondition'] = 'match_phrase_prefix';
                }
            }
        }
        $this->localData->setData([$requestName => $data]);
    }

    /**
     * Check if and what type of match_type value was requested
     *
     * @param array $args
     * @return array
     */
    private function getPartialMatchFilters(array $args): array
    {
        $partialMatchFilters = [];
        foreach ($args['filter'] as $fieldName => $conditions) {
            if (isset($conditions['match_type']) && $conditions['match_type'] === 'PARTIAL') {
                $partialMatchFilters[] = $fieldName;
            }
        }
        return $partialMatchFilters;
    }

    /**
     * Remove the match_type to avoid search criteria containing it
     *
     * @param array $args
     * @return array
     */
    private function removeMatchTypeFromArguments(array $args): array
    {
        foreach ($args['filter'] as &$conditions) {
            if (isset($conditions['match_type'])) {
                unset($conditions['match_type']);
            }
        }

        return $args;
    }

    /**
     * Add filter by visibility
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param bool $isSearch
     * @param bool $isFilter
     */
    private function addVisibilityFilter(SearchCriteriaInterface $searchCriteria, bool $isSearch, bool $isFilter): void
    {
        if ($isFilter && $isSearch) {
            // Index already contains products filtered by visibility: catalog, search, both
            return;
        }
        $visibilityIds = $isSearch
            ? $this->visibility->getVisibleInSearchIds()
            : $this->visibility->getVisibleInCatalogIds();

        $this->addFilter($searchCriteria, 'visibility', $visibilityIds, 'in');
    }

    /**
     * Add sort by Entity ID
     *
     * @param SearchCriteriaInterface $searchCriteria
     */
    private function addEntityIdSort(SearchCriteriaInterface $searchCriteria): void
    {
        $sortOrderArray = $searchCriteria->getSortOrders();
        $sortDir = SortOrder::SORT_DESC;
        if (is_array($sortOrderArray) && count($sortOrderArray) > 0) {
            $sortOrder = end($sortOrderArray);
            // in the case the last sort order is by position, sort IDs in descendent order
            $sortDir = $sortOrder->getField() === EavAttributeInterface::POSITION
                ? SortOrder::SORT_DESC
                : $sortOrder->getDirection();
        }

        $sortOrderArray[] = $this->sortOrderBuilder
            ->setField('entity_id')
            ->setDirection($sortDir)
            ->create();
        $searchCriteria->setSortOrders($sortOrderArray);
    }

    /**
     * Prepare price aggregation algorithm
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param bool $includeAggregation
     * @return void
     */
    private function preparePriceAggregation(SearchCriteriaInterface $searchCriteria, bool $includeAggregation): void
    {
        if (!$includeAggregation) {
            return;
        }

        $attributeData = $this->productAttributeRepository->get('price');
        $priceOptions = $attributeData->getData();
        if ((int) $priceOptions['is_filterable'] === 0) {
            return;
        }

        $priceRangeCalculation = $this->scopeConfig->getValue(
            \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($priceRangeCalculation) {
            $this->addFilter($searchCriteria, 'price_dynamic_algorithm', $priceRangeCalculation);
        }
    }

    /**
     * Add filter to search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param string $field
     * @param mixed $value
     * @param string|null $condition
     */
    private function addFilter(
        SearchCriteriaInterface $searchCriteria,
        string $field,
        $value,
        ?string $condition = null
    ): void {
        $filter = $this->filterBuilder
            ->setField($field)
            ->setValue($value)
            ->setConditionType($condition)
            ->create();

        $this->filterGroupBuilder->addFilter($filter);
        $filterGroups = $searchCriteria->getFilterGroups();
        $filterGroups[] = $this->filterGroupBuilder->create();
        $searchCriteria->setFilterGroups($filterGroups);
    }

    /**
     * Sort by relevance DESC by default
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $args
     * @param bool $isSearch
     */
    private function addDefaultSortOrder(SearchCriteriaInterface $searchCriteria, array $args, $isSearch = false): void
    {
        $defaultSortOrder = [];
        if ($isSearch) {
            $defaultSortOrder[] = $this->sortOrderBuilder
                ->setField('relevance')
                ->setDirection(SortOrder::SORT_DESC)
                ->create();
        } else {
            $categoryIdFilter = $args['filter']['category_id'] ?? false;
            if ($categoryIdFilter) {
                if (!is_array($categoryIdFilter[array_key_first($categoryIdFilter)])
                    || count($categoryIdFilter[array_key_first($categoryIdFilter)]) <= 1
                ) {
                    $defaultSortOrder[] = $this->sortOrderBuilder
                        ->setField(EavAttributeInterface::POSITION)
                        ->setDirection(SortOrder::SORT_ASC)
                        ->create();
                }
            }
        }

        $searchCriteria->setSortOrders($defaultSortOrder);
    }

    /**
     * Format range filters so replacement works
     *
     * Range filter fields in search request must replace value like '%field.from%' or '%field.to%'
     *
     * @param SearchCriteriaInterface $searchCriteria
     */
    private function updateRangeFilters(SearchCriteriaInterface $searchCriteria): void
    {
        $filterGroups = $searchCriteria->getFilterGroups();
        foreach ($filterGroups as $filterGroup) {
            $filters = $filterGroup->getFilters();
            foreach ($filters as $filter) {
                if (in_array($filter->getConditionType(), ['from', 'to'])) {
                    $filter->setField($filter->getField() . '.' . $filter->getConditionType());
                }
            }
        }
    }
}
