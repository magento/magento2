<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Build search criteria
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
     * @param Builder $builder
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param Visibility $visibility
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        Builder $builder,
        ScopeConfigInterface $scopeConfig,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        Visibility $visibility,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->builder = $builder;
        $this->visibility = $visibility;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Build search criteria
     *
     * @param array $args
     * @param bool $includeAggregation
     * @return SearchCriteriaInterface
     */
    public function build(array $args, bool $includeAggregation): SearchCriteriaInterface
    {
        $searchCriteria = $this->builder->build('products', $args);
        $isSearch = !empty($args['search']);
        $this->updateRangeFilters($searchCriteria);

        if ($includeAggregation) {
            $this->preparePriceAggregation($searchCriteria);
            $requestName = 'graphql_product_search_with_aggregation';
        } else {
            $requestName = 'graphql_product_search';
        }
        $searchCriteria->setRequestName($requestName);

        if ($isSearch) {
            $this->addFilter($searchCriteria, 'search_term', $args['search']);
        }

        if (!$searchCriteria->getSortOrders()) {
            $this->addDefaultSortOrder($searchCriteria, $isSearch);
        }

        $this->addVisibilityFilter($searchCriteria, $isSearch, !empty($args['filter']));

        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);

        return $searchCriteria;
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
     * @param bool $isSearch
     */
    private function addDefaultSortOrder(SearchCriteriaInterface $searchCriteria, $isSearch = false): void
    {
        $sortField = $isSearch ? 'relevance' : EavAttributeInterface::POSITION;
        $sortDirection = $isSearch ? SortOrder::SORT_DESC : SortOrder::SORT_ASC;
        $defaultSortOrder = $this->sortOrderBuilder
            ->setField($sortField)
            ->setDirection($sortDirection)
            ->create();

        $searchCriteria->setSortOrders([$defaultSortOrder]);
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
