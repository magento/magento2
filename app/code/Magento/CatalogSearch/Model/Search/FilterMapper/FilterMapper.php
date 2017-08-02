<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogInventory\Model\Stock;

/**
 * Class FilterMapper
 * This class applies filters to Select based on SelectContainer configuration
 * @since 2.2.0
 */
class FilterMapper
{
    /**
     * @var AliasResolver
     * @since 2.2.0
     */
    private $aliasResolver;

    /**
     * @var CustomAttributeFilter
     * @since 2.2.0
     */
    private $customAttributeFilter;

    /**
     * @var FilterStrategyInterface
     * @since 2.2.0
     */
    private $filterStrategy;

    /**
     * @var VisibilityFilter
     * @since 2.2.0
     */
    private $visibilityFilter;

    /**
     * @var StockStatusFilter
     * @since 2.2.0
     */
    private $stockStatusFilter;

    /**
     * @param AliasResolver $aliasResolver
     * @param CustomAttributeFilter $customAttributeFilter
     * @param FilterStrategyInterface $filterStrategy
     * @param VisibilityFilter $visibilityFilter
     * @param StockStatusFilter $stockStatusFilter
     * @since 2.2.0
     */
    public function __construct(
        AliasResolver $aliasResolver,
        CustomAttributeFilter $customAttributeFilter,
        FilterStrategyInterface $filterStrategy,
        VisibilityFilter $visibilityFilter,
        StockStatusFilter $stockStatusFilter
    ) {
        $this->aliasResolver = $aliasResolver;
        $this->customAttributeFilter = $customAttributeFilter;
        $this->filterStrategy = $filterStrategy;
        $this->visibilityFilter = $visibilityFilter;
        $this->stockStatusFilter = $stockStatusFilter;
    }

    /**
     * Applies filters to Select query in SelectContainer
     * based on SelectContainer configuration
     *
     * @param SelectContainer $selectContainer
     * @return SelectContainer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     * @throws \DomainException
     * @since 2.2.0
     */
    public function applyFilters(SelectContainer $selectContainer)
    {
        $select = $selectContainer->getSelect();

        if ($selectContainer->hasCustomAttributesFilters()) {
            $select = $this->customAttributeFilter->apply($select, ...$selectContainer->getCustomAttributesFilters());
        }

        $filterType = StockStatusFilter::FILTER_JUST_ENTITY;
        if ($selectContainer->hasCustomAttributesFilters()) {
            $filterType = StockStatusFilter::FILTER_ENTITY_AND_SUB_PRODUCTS;
        }

        $select = $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            $filterType,
            $selectContainer->isShowOutOfStockEnabled()
        );

        $appliedFilters = [];

        if ($selectContainer->hasVisibilityFilter()) {
            $filterType = VisibilityFilter::FILTER_BY_WHERE;
            if ($selectContainer->hasCustomAttributesFilters()) {
                $filterType = VisibilityFilter::FILTER_BY_JOIN;
            }

            $select = $this->visibilityFilter->apply($select, $selectContainer->getVisibilityFilter(), $filterType);
            $appliedFilters[$this->aliasResolver->getAlias($selectContainer->getVisibilityFilter())] = true;
        }

        foreach ($selectContainer->getNonCustomAttributesFilters() as $filter) {
            $alias = $this->aliasResolver->getAlias($filter);

            if (!array_key_exists($alias, $appliedFilters)) {
                $isApplied = $this->filterStrategy->apply($filter, $select);
                if ($isApplied) {
                    $appliedFilters[$alias] = true;
                }
            }
        }

        $selectContainer = $selectContainer->updateSelect($select);
        return $selectContainer;
    }
}
