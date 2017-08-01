<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

/**
 * Interface StockStateInterface
 * @api
 * @since 2.0.0
 */
interface StockStateInterface
{
    /**
     * @param int $productId
     * @param int $scopeId
     * @return bool
     * @since 2.0.0
     */
    public function verifyStock($productId, $scopeId = null);

    /**
     * @param int $productId
     * @param int $scopeId
     * @return bool
     * @since 2.0.0
     */
    public function verifyNotification($productId, $scopeId = null);

    /**
     * @param int $productId
     * @param float $itemQty
     * @param float $qtyToCheck
     * @param float $origQty
     * @param int $scopeId
     * @return int
     * @since 2.0.0
     */
    public function checkQuoteItemQty($productId, $itemQty, $qtyToCheck, $origQty, $scopeId = null);

    /**
     * Check quantity
     *
     * @param int $productId
     * @param float $qty
     * @param int $scopeId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     * @since 2.0.0
     */
    public function checkQty($productId, $qty, $scopeId = null);

    /**
     * Returns suggested qty that satisfies qty increments and minQty/maxQty/minSaleQty/maxSaleQty conditions
     * or original qty if such value does not exist
     *
     * @param int $productId
     * @param float $qty
     * @param int $scopeId
     * @return float
     * @since 2.0.0
     */
    public function suggestQty($productId, $qty, $scopeId = null);

    /**
     * Retrieve stock qty whether product is composite or no
     *
     * @param int $productId
     * @param int $scopeId
     * @return float
     * @since 2.0.0
     */
    public function getStockQty($productId, $scopeId = null);
}
