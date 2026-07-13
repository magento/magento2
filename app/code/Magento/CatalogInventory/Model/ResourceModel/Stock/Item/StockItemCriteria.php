<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Stock\Item;

use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\Framework\Data\AbstractCriteria;

/**
 * Class StockItemCriteria Resource model
 */
class StockItemCriteria extends AbstractCriteria implements StockItemCriteriaInterface
{
    /**
     * @param string $mapper
     */
    public function __construct($mapper = '')
    {
        $this->mapperInterfaceName = $mapper ?: StockItemCriteriaMapper::class;
        $this->data['initial_condition'] = [true];
    }

    /**
     * @inheritdoc
     */
    public function setStockStatus($storeId = null)
    {
        $this->data['stock_status'] = func_get_args();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setStockFilter($stock)
    {
        $this->data['stock_filter'] = [$stock];
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setScopeFilter($scope)
    {
        $this->data['website_filter'] = [$scope];
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setProductsFilter($products)
    {
        $this->data['products_filter'] = [$products];
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setManagedFilter($isStockManagedInConfig)
    {
        $this->data['managed_filter'] = [$isStockManagedInConfig];
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setQtyFilter($comparisonMethod, $qty)
    {
        $this->data['qty_filter'] = [$comparisonMethod, $qty];
        return true;
    }

    /**
     * Add Criteria object
     *
     * @param StockItemCriteriaInterface $criteria
     * @return bool
     */
    public function addCriteria(StockItemCriteriaInterface $criteria)
    {
        $this->data[self::PART_CRITERIA_LIST]['list'][] = $criteria;
        return true;
    }
}
