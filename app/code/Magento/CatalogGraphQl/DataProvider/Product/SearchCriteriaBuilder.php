<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use Magento\Framework\Search\Request\Config as SearchConfig;

/**
 * Build search criteria
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class SearchCriteriaBuilder
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var Config
     */
    private Config $eavConfig;

    /**
     * @var SearchConfig
     */
    private SearchConfig $searchConfig;

    /**
     * @var RequestDataBuilder|mixed
     */
    private RequestDataBuilder $localData;

    /**
     * @param Builder $builder
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param Visibility $visibility
     * @param SortOrderBuilder|null $sortOrderBuilder
     * @param Config|null $eavConfig
     * @param SearchConfig|null $searchConfig
     * @param RequestDataBuilder|null $localData
     */
    public function __construct(
        Builder $builder,
        ScopeConfigInterface $scopeConfig,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        Visibility $visibility,
        SortOrderBuilder $sortOrderBuilder = null,
        Config $eavConfig = null,
        SearchConfig $searchConfig = null,
        RequestDataBuilder $localData = null,
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->builder = $builder;
        $this->visibility = $visibility;
        $this->sortOrderBuilder = $sortOrderBuilder ?? ObjectManager::getInstance()->get(SortOrderBuilder::class);
        $this->eavConfig = $eavConfig ?? ObjectManager::getInstance()->get(Config::class);
        $this->searchConfig = $searchConfig ?? ObjectManager::getInstance()->get(SearchConfig::class);
        $this->localData = $localData ?? ObjectManager::getInstance()->get(RequestDataBuilder::class);
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
        $partialMatchFilters = [];
        if (isset($args['filter'])) {
            $partialMatchFilters = $this->getPartialMatchFilters($args);
            $args = $this->removeMatchTypeFromArguments($args);
        }
        $searchCriteria = $this->builder->build('products', $args);
        $isSearch = isset($args['search']);
        $this->updateRangeFilters($searchCriteria);
        if ($includeAggregation) {
            $attributeData = $this->eavConfig->getAttribute(Product::ENTITY, 'price');
            $priceOptions = $attributeData->getData();

            if ($priceOptions['is_filterable'] != 0) {
                $this->preparePriceAggregation($searchCriteria);
            }
            $requestName = 'graphql_product_search_with_aggregation';
        } else {
            $requestName = 'graphql_product_search';
        }
        $searchCriteria->setRequestName($requestName);

        if (count($partialMatchFilters)) {
            $this->updateMatchTypeRequestConfig($requestName, $partialMatchFilters);
        }

        if ($isSearch) {
            $this->addFilter($searchCriteria, 'search_term', $args['search']);
        }

        if (!$searchCriteria->getSortOrders()) {
            $this->addDefaultSortOrder($searchCriteria, $args, $isSearch);
        }

        $this->addEntityIdSort($searchCriteria);
        $this->addVisibilityFilter($searchCriteria, $isSearch, !empty($args['filter']['category_id']));

        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);

        return $searchCriteria;
    }

    /**
     * Update dynamically the search match type based on requested params
     *
     * @param string $requestName
     * @param array $partialMatchFilters
     *
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
     *
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
     *
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
            ->setField('_id')
            ->setDirection($sortDir)
            ->create();
        $searchCriteria->setSortOrders($sortOrderArray);
    }

    /**
     * Prepare price aggregation algorithm
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return void
     */
    private function preparePriceAggregation(SearchCriteriaInterface $searchCriteria): void
    {
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
            $categoryIdFilter = isset($args['filter']['category_id']) ? $args['filter']['category_id'] : false;
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
