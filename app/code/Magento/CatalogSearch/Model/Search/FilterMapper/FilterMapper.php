<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ObjectManager;

/**
 * This class applies filters to Select based on SelectContainer configuration
 *
 * @deprecated 101.0.0 MySQL search engine is not recommended.
 * @see \Magento\ElasticSearch
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var CustomAttributeStockStatusFilter
     */
    private $customAttributeStockStatusFilter;

    /**
     * @param AliasResolver $aliasResolver
     * @param CustomAttributeFilter $customAttributeFilter
     * @param FilterStrategyInterface $filterStrategy
     * @param VisibilityFilter $visibilityFilter
     * @param StockStatusFilter $stockStatusFilter
     * @param CustomAttributeStockStatusFilter|null $customAttributeStockStatusFilter
     */
    public function __construct(
        AliasResolver $aliasResolver,
        CustomAttributeFilter $customAttributeFilter,
        FilterStrategyInterface $filterStrategy,
        VisibilityFilter $visibilityFilter,
        StockStatusFilter $stockStatusFilter,
        ?CustomAttributeStockStatusFilter $customAttributeStockStatusFilter = null
    ) {
        $this->aliasResolver = $aliasResolver;
        $this->customAttributeFilter = $customAttributeFilter;
        $this->filterStrategy = $filterStrategy;
        $this->visibilityFilter = $visibilityFilter;
        $this->stockStatusFilter = $stockStatusFilter;
        $this->customAttributeStockStatusFilter = $customAttributeStockStatusFilter
            ?? ObjectManager::getInstance()->get(CustomAttributeStockStatusFilter::class);
    }

    /**
     * Applies filters to Select query in SelectContainer based on SelectContainer configuration
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

        $select = $this->stockStatusFilter->apply(
            $select,
            Stock::STOCK_IN_STOCK,
            StockStatusFilter::FILTER_JUST_ENTITY,
            $selectContainer->isShowOutOfStockEnabled()
        );

        if ($selectContainer->hasCustomAttributesFilters()) {
            $select = $this->customAttributeFilter->apply($select, ...$selectContainer->getCustomAttributesFilters());
            $select = $this->customAttributeStockStatusFilter->apply(
                $select,
                $selectContainer->isShowOutOfStockEnabled() ? null : Stock::STOCK_IN_STOCK,
                ...$selectContainer->getCustomAttributesFilters()
            );
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
}
