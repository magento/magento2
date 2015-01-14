<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockStatusCriteriaInterface
 */
interface StockStatusCriteriaInterface extends \Magento\Framework\Api\CriteriaInterface
{
    /**
     * Add Criteria object
     *
     * @param \Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria
     * @return bool
     */
    public function addCriteria(\Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria);

    /**
     * Filter by website(s)
     *
     * @param int $website
     * @return bool
     */
    public function setWebsiteFilter($website);

    /**
     * Add product(s) filter
     *
     * @param int $products
     * @return bool
     */
    public function setProductsFilter($products);

    /**
     * Add filter by quantity
     *
     * @param float $qty
     * @return bool
     */
    public function setQtyFilter($qty);
}
