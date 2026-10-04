<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\Framework\Data\AbstractCriteria;

/**
 * Class StockStatusCriteria Resource model
 */
class StockStatusCriteria extends AbstractCriteria implements StockStatusCriteriaInterface
{
    /**
     * @param string $mapper
     */
    public function __construct($mapper = '')
    {
        $this->mapperInterfaceName = $mapper ?: StockStatusCriteriaMapper::class;
        $this->data['initial_condition'] = [true];
    }

    /**
     * Filter by scope(s)
     *
     * @param int $scope
     * @return void
     */
    public function setScopeFilter($scope)
    {
        $this->data['website_filter'] = [$scope];
    }

    /**
     * Add product(s) filter
     *
     * @param int $products
     * @return void
     */
    public function setProductsFilter($products)
    {
        $this->data['products_filter'] = [$products];
    }

    /**
     * Add filter by quantity
     *
     * @param float $qty
     * @return void
     */
    public function setQtyFilter($qty)
    {
        $this->data['qty_filter'] = [$qty];
    }

    /**
     * Add Criteria object
     *
     * @param \Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria
     * @return void
     */
    public function addCriteria(StockStatusCriteriaInterface $criteria)
    {
        $this->data[self::PART_CRITERIA_LIST]['list'][] = $criteria;
    }
}
