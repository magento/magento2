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
 * Interface StockRegistryInterface
 */
interface StockRegistryInterface
{
    /**
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     */
    public function getStock($websiteId = null);

    /**
     * @param int $productId
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId, $websiteId = null);

    /**
     * @param string $productSku
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItemBySku($productSku, $websiteId = null);

    /**
     * @param int $productId
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     */
    public function getStockStatus($productId, $websiteId = null);

    /**
     * @param string $productSku
     * @param int $websiteId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockStatusBySku($productSku, $websiteId = null);

    /**
     * Retrieve Product stock status
     *
     * @param int $productId
     * @param int $websiteId
     * @return int
     */
    public function getProductStockStatus($productId, $websiteId = null);

    /**
     * @param string $productSku
     * @param int $websiteId
     * @return int
     * @throw \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductStockStatusBySku($productSku, $websiteId = null);

    /**
     * Retrieves a list of SKU's with low inventory qty
     *
     * @param int $websiteId
     * @param float $qty
     * @param int $currentPage
     * @param int $pageSize
     * @return \Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface
     */
    public function getLowStockItems($websiteId, $qty, $currentPage = 1, $pageSize = 0);

    /**
     * @param string $productSku
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateStockItemBySku($productSku, \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem);
}
