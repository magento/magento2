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
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Responsibility of the TableMapper is to collect all filters from the search query
 * and pass them one by one for processing in the FilterContext,
 * which will apply them to the Select.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class TableMapper
{
    /**
     * Resource connection.
     *
     * @var AppResource
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @deprecated
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    private $attributeCollection;

    /**
     * Eav attribute config.
     *
     * @var EavConfig
     */
     private $eavConfig;

    /**
     * Scope config.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * FilterStrategyInterface provides the interface to work with strategies.
     *
     * @var FilterStrategyInterface
     */
    private $filterStrategy;

    /**
     * Table alias resolver for Search Request filter.
     *
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * TableMapper constructor.
     * @param AppResource $resource
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $attributeCollectionFactory
     * @param EavConfig|null $eavConfig
     * @param ScopeConfigInterface|null $scopeConfig
     * @param FilterStrategyInterface|null $filterStrategy
     * @param AliasResolver|null $aliasResolver
     */
    public function __construct(
        AppResource $resource,
        StoreManagerInterface $storeManager,
        CollectionFactory $attributeCollectionFactory,
        EavConfig $eavConfig = null,
        ScopeConfigInterface $scopeConfig = null,
        FilterStrategyInterface $filterStrategy = null,
        AliasResolver $aliasResolver = null
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->attributeCollection = $attributeCollectionFactory->create();
        $this->eavConfig = $eavConfig ?: ObjectManager::getInstance()->get(EavConfig::class);
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->filterStrategy = $filterStrategy ?: ObjectManager::getInstance()->get(FilterStrategyInterface::class);
        $this->aliasResolver = $aliasResolver ?: ObjectManager::getInstance()->get(AliasResolver::class);
    }

    /**
     * Adds tables to select.
     *
     * @param Select $select
     * @param RequestInterface $request
     *
     * @return Select
     */
    public function addTables(Select $select, RequestInterface $request)
    {
        $appliedFilters = [];
        $filters = $this->getFiltersFromQuery($request->getQuery());

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

    /**
     * @param RequestQueryInterface $query
     * @return FilterInterface[]
     */
    private function getFiltersFromQuery(RequestQueryInterface  $query)
    {
        $filters = [];
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Query\BoolExpression $query */
                foreach ($query->getMust() as $subQuery) {
                    $filters = array_merge($filters, $this->getFiltersFromQuery($subQuery));
                }
                foreach ($query->getShould() as $subQuery) {
                    $filters = array_merge($filters, $this->getFiltersFromQuery($subQuery));
                }
                foreach ($query->getMustNot() as $subQuery) {
                    $filters = array_merge($filters, $this->getFiltersFromQuery($subQuery));
                }
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var Filter $query */
                $filter = $query->getReference();
                if (FilterInterface::TYPE_BOOL === $filter->getType()) {
                    $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
                } else {
                    $filters[] = $filter;
                }
                break;
            default:
                break;
        }
        return $filters;
    }

    /**
     * @param BoolExpression $boolExpression
     * @return FilterInterface[]
     */
    private function getFiltersFromBoolFilter(BoolExpression $boolExpression)
    {
        $filters = [];
        /** @var BoolExpression $filter */
        foreach ($boolExpression->getMust() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getShould() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getMustNot() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        return $filters;
    }
}
