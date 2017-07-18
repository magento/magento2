<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogSearch\Model\Search\FilterMapper\FilterStrategyInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Responsibility of the TableMapper is to collect all filters from the search query
 * and pass them one by one for processing in the FilterContext,
 * which will apply them to the Select
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 */
class TableMapper
{
    /**
     * @var FilterStrategyInterface
     */
    private $filterStrategy;

    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @var AliasResolver
     */
    private $filtersExtractor;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param AppResource $resource
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $attributeCollectionFactory
     * @param EavConfig $eavConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterStrategyInterface $filterStrategy
     * @param AliasResolver $aliasResolver
     * @param FiltersExtractor $filtersExtractor
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        AppResource $resource,
        StoreManagerInterface $storeManager,
        CollectionFactory $attributeCollectionFactory,
        EavConfig $eavConfig = null,
        ScopeConfigInterface $scopeConfig = null,
        FilterStrategyInterface $filterStrategy = null,
        AliasResolver $aliasResolver = null,
        FiltersExtractor $filtersExtractor = null
    ) {
        if (null === $filterStrategy) {
            $filterStrategy = ObjectManager::getInstance()->get(FilterStrategyInterface::class);
        }
        if (null === $aliasResolver) {
            $aliasResolver = ObjectManager::getInstance()->get(AliasResolver::class);
        }
        if (null === $filtersExtractor) {
            $filtersExtractor = ObjectManager::getInstance()->get(FiltersExtractor::class);
        }

        $this->filterStrategy = $filterStrategy;
        $this->aliasResolver = $aliasResolver;
        $this->filtersExtractor = $filtersExtractor;
    }

    /**
     * @param Select $select
     * @param RequestInterface $request
     * @return Select
     * @throws \LogicException
     */
    public function addTables(Select $select, RequestInterface $request)
    {
        $appliedFilters = [];
        $filters = $this->filtersExtractor->extractFiltersFromQuery($request->getQuery());
        foreach ($filters as $filter) {
            $alias = $this->aliasResolver->getAlias($filter);
            if (!array_key_exists($alias, $appliedFilters)) {
                $isApplied = $this->filterStrategy->apply($filter, $select);
                if ($isApplied) {
                    $appliedFilters[$alias] = true;
                }
            }
        }
        return $select;
    }

    /**
     * This method is deprecated.
     * Please use \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver::getAlias() instead.
     *
     * @deprecated
     * @see AliasResolver::getAlias()
     *
     * @param FilterInterface $filter
     * @return string
     */
    public function getMappingAlias(FilterInterface $filter)
    {
        return $this->aliasResolver->getAlias($filter);
    }
}
