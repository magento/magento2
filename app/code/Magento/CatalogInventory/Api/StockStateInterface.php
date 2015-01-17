<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockStateInterface
 */
interface StockStateInterface
{
    /**
     * @param int $productId
     * @param int $websiteId
     * @return bool
     */
    public function verifyStock($productId, $websiteId = null);

    /**
     * @param int $productId
     * @param int $websiteId
     * @return bool
     */
    public function verifyNotification($productId, $websiteId = null);

    /**
     * @param int $productId
     * @param float $itemQty
     * @param float $qtyToCheck
     * @param float $origQty
     * @param int $websiteId
     * @return int
     */
    public function checkQuoteItemQty($productId, $itemQty, $qtyToCheck, $origQty, $websiteId = null);

    /**
     * Check quantity
     *
     * @param int $productId
     * @param float $qty
     * @param int $websiteId
     * @throws \Magento\Framework\Model\Exception
     * @return bool
     */
    public function checkQty($productId, $qty, $websiteId = null);

    /**
     * Returns suggested qty that satisfies qty increments and minQty/maxQty/minSaleQty/maxSaleQty conditions
     * or original qty if such value does not exist
     *
     * @param int $productId
     * @param float $qty
     * @param int $websiteId
     * @return float
     */
    public function suggestQty($productId, $qty, $websiteId = null);

    /**
     * Retrieve stock qty whether product is composite or no
     *
     * @param int $productId
     * @param int $websiteId
     * @return float
     */
    public function getStockQty($productId, $websiteId = null);
}
