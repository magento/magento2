<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogInventory\Model\ResourceModel\Stock\Item;

use Magento\Framework\Data\AbstractCriteria;

/**
 * Class StockItemCriteria
 * @since 2.0.0
 */
class StockItemCriteria extends AbstractCriteria implements \Magento\CatalogInventory\Api\StockItemCriteriaInterface
{
    /**
     * @param string $mapper
     * @since 2.0.0
     */
    public function __construct($mapper = '')
    {
        $this->mapperInterfaceName = $mapper ?: \Magento\CatalogInventory\Model\ResourceModel\Stock\Item\StockItemCriteriaMapper::class;
        $this->data['initial_condition'] = true;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setStockStatus($storeId = null)
    {
        $this->data['stock_status'] = func_get_args();
        return true;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setStockFilter($stock)
    {
        $this->data['stock_filter'] = $stock;
        return true;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setScopeFilter($scope)
    {
        $this->data['website_filter'] = $scope;
        return true;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setProductsFilter($products)
    {
        $this->data['products_filter'] = $products;
        return true;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setManagedFilter($isStockManagedInConfig)
    {
        $this->data['managed_filter'] = $isStockManagedInConfig;
        return true;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function setQtyFilter($comparisonMethod, $qty)
    {
        $this->data['qty_filter'] = [$comparisonMethod, $qty];
        return true;
    }

    /**
     * Add Criteria object
     *
     * @param \Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria
     * @return bool
     * @since 2.0.0
     */
    public function addCriteria(\Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria)
    {
        $this->data[self::PART_CRITERIA_LIST]['list'][] = $criteria;
        return true;
    }
}
