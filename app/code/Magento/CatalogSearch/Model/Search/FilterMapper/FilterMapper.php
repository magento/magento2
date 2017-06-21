<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogSearch\Model\Search\FilterMapper\StockStatusFilter;
use Magento\CatalogInventory\Model\Stock;

/**
 * Class FilterMapper
 * This class applies filters to Select based on SelectContainer configuration
 */
class FilterMapper
{
    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @var CustomAttributeFilter
     */
    private $customAttributeFilter;

    /**
     * @var FilterStrategyInterface
     */
    private $filterStrategy;

    /**
     * @var VisibilityFilter
     */
    private $visibilityFilter;

    /**
     * @var StockStatusFilter
     */
    private $stockStatusFilter;

    /**
     * @param AliasResolver $aliasResolver
     * @param CustomAttributeFilter $customAttributeFilter
     * @param FilterStrategyInterface $filterStrategy
     * @param VisibilityFilter $visibilityFilter
     * @param StockStatusFilter $stockStatusFilter
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
     */
    public function applyFilters(SelectContainer $selectContainer)
    {
        $select = $selectContainer->getSelect();

        if ($selectContainer->hasCustomAttributesFilters()) {
            $select = $this->customAttributeFilter->apply($select, ...$selectContainer->getCustomAttributesFilters());
        }

        if (!$selectContainer->isShowOutOfStockEnabled()) {

            $filterType = StockStatusFilter::FILTER_JUST_ENTITY;
            if ($selectContainer->hasCustomAttributesFilters()) {
                $filterType = StockStatusFilter::FILTER_ENTITY_AND_SOURCE;
            }

            $select = $this->stockStatusFilter->apply($select, Stock::STOCK_IN_STOCK, $filterType);
        }

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

    /**
     * This method is deprecated.
     * Please use \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver::getAlias() instead.
     *
     * @deprecated
     * @see AliasResolver::getAlias()
     *
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @return string
     */
    public function getMappingAlias(\Magento\Framework\Search\Request\FilterInterface $filter)
    {
        return $this->aliasResolver->getAlias($filter);
    }
}
