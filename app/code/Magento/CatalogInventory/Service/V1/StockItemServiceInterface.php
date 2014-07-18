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
namespace Magento\CatalogInventory\Service\V1;

/**
 * Stock item interface
 */
interface StockItemServiceInterface
{
    /**
     * @param int $productId
     * @return \Magento\CatalogInventory\Service\V1\Data\StockItem
     */
    public function getStockItem($productId);

    /**
     * @param string $productSku
     * @return \Magento\CatalogInventory\Service\V1\Data\StockItem
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItemBySku($productSku);

    /**
     * @param \Magento\CatalogInventory\Service\V1\Data\StockItem $stockItem
     * @return \Magento\CatalogInventory\Service\V1\Data\StockItem
     */
    public function saveStockItem($stockItem);

    /**
     * @param string $productSku
     * @param \Magento\CatalogInventory\Service\V1\Data\StockItemDetails $stockItemDetailsDo
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveStockItemBySku($productSku, Data\StockItemDetails $stockItemDetailsDo);

    /**
     * @param int $productId
     * @return int
     */
    public function getMinSaleQty($productId);

    /**
     * @param int $productId
     * @return int
     */
    public function getMaxSaleQty($productId);

    /**
     * @param int $productId
     * @return bool
     */
    public function getEnableQtyIncrements($productId);

    /**
     * @param int $productId
     * @return int
     */
    public function getQtyIncrements($productId);

    /**
     * @param int $productId
     * @return int mixed
     */
    public function getManageStock($productId);

    /**
     * @param int $productId
     * @param int $qty
     * @return bool
     */
    public function suggestQty($productId, $qty);

    /**
     * @param int $productId
     * @param int $qty
     * @param int $summaryQty
     * @param int $origQty
     * @return int
     */
    public function checkQuoteItemQty($productId, $qty, $summaryQty, $origQty = 0);

    /**
     * @param int $productId
     * @param int|null $qty
     * @return bool
     */
    public function verifyStock($productId, $qty = null);

    /**
     * @param int $productId
     * @param int|null $qty
     * @return bool
     */
    public function verifyNotification($productId, $qty = null);

    /**
     * @param int $productId
     * @return bool
     */
    public function getIsInStock($productId);

    /**
     * @param int $productId
     * @return int
     */
    public function getStockQty($productId);

    /**
     * @param int $productTypeId
     * @return bool
     */
    public function isQty($productTypeId);

    /**
     * @param int|null $filter
     * @return bool
     */
    public function getIsQtyTypeIds($filter = null);

    /**
     * @param int $stockData
     * @return array
     */
    public function processIsInStock($stockData);
}
