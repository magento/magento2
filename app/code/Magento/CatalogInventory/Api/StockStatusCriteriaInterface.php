<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockStatusCriteriaInterface
 * @api
 * @since 2.0.0
 */
interface StockStatusCriteriaInterface extends \Magento\Framework\Api\CriteriaInterface
{
    /**
     * Add Criteria object
     *
     * @param \Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria
     * @return bool
     * @since 2.0.0
     */
    public function addCriteria(\Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria);

    /**
     * Filter by scope(s)
     *
     * @param int $scope
     * @return bool
     * @since 2.0.0
     */
    public function setScopeFilter($scope);

    /**
     * Add product(s) filter
     *
     * @param int $products
     * @return bool
     * @since 2.0.0
     */
    public function setProductsFilter($products);

    /**
     * Add filter by quantity
     *
     * @param float $qty
     * @return bool
     * @since 2.0.0
     */
    public function setQtyFilter($qty);
}
