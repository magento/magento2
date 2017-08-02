<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockCriteriaInterface
 * @api
 * @since 2.0.0
 */
interface StockCriteriaInterface extends \Magento\Framework\Api\CriteriaInterface
{
    /**
     * Add Criteria object
     *
     * @param \Magento\CatalogInventory\Api\StockCriteriaInterface $criteria
     * @return bool
     * @since 2.0.0
     */
    public function addCriteria(\Magento\CatalogInventory\Api\StockCriteriaInterface $criteria);

    /**
     * Add scope filter to collection
     *
     * @param int $scope
     * @return bool
     * @since 2.0.0
     */
    public function setScopeFilter($scope);
}
