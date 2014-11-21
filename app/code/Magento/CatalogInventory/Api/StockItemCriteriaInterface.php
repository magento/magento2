<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockItemCriteriaInterface
 */
interface StockItemCriteriaInterface extends \Magento\Framework\Api\CriteriaInterface
{
    /**
     * Add Criteria object
     *
     * @param \Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria
     * @return void
     */
    public function addCriteria(\Magento\CatalogInventory\Api\StockItemCriteriaInterface $criteria);

    /**
     * Join Stock Status to collection
     *
     * @param int $storeId
     * @return void
     */
    public function setStockStatus($storeId = null);

    /**
     * Add stock filter to collection
     *
     * @param \Magento\CatalogInventory\Api\Data\StockInterface|string|array $stock
     * @return void
     */
    public function setStockFilter($stock);

    /**
     * Add website filter to collection
     *
     * @param array|int|object $website
     * @return void
     */
    public function setWebsiteFilter($website);

    /**
     * Add product filter to collection
     *
     * @param array|int|object $products
     * @return void
     */
    public function setProductsFilter($products);

    /**
     * Add Managed Stock products filter to collection
     *
     * @param bool $isStockManagedInConfig
     * @return void
     */
    public function setManagedFilter($isStockManagedInConfig);

    /**
     * Add filter by quantity to collection
     *
     * @param string $comparisonMethod
     * @param float $qty
     * @return void
     */
    public function setQtyFilter($comparisonMethod, $qty);
}
