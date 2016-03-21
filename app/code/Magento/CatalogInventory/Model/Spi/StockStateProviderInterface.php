<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Spi;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * Interface StockStateProviderInterface
 */
interface StockStateProviderInterface
{
    /**
     * @param StockItemInterface $stockItem
     * @return bool
     */
    public function verifyStock(StockItemInterface $stockItem);

    /**
     * @param StockItemInterface $stockItem
     * @return bool
     */
    public function verifyNotification(StockItemInterface $stockItem);

    /**
     * @param StockItemInterface $stockItem
     * @param int|float $itemQty
     * @param int|float $qtyToCheck
     * @param int|float $origQty
     * @return int
     */
    public function checkQuoteItemQty(StockItemInterface $stockItem, $itemQty, $qtyToCheck, $origQty = 0);

    /**
     * Check quantity
     *
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @exception \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function checkQty(StockItemInterface $stockItem, $qty);

    /**
     * Returns suggested qty that satisfies qty increments and minQty/maxQty/minSaleQty/maxSaleQty conditions
     * or original qty if such value does not exist
     *
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @return int|float
     */
    public function suggestQty(StockItemInterface $stockItem, $qty);

    /**
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @return \Magento\Framework\DataObject
     */
    public function checkQtyIncrements(StockItemInterface $stockItem, $qty);

    /**
     * Retrieve stock qty whether product is composite or no
     *
     * @param StockItemInterface $stockItem
     * @return float
     */
    public function getStockQty(StockItemInterface $stockItem);
}
