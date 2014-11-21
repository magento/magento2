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
namespace Magento\CatalogInventory\Model\Spi;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * Interface StockStateProviderInterface
 * @package Magento\CatalogInventory\Model\Spi
 * @spi
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
     * @exception \Magento\Framework\Model\Exception
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
     * @return \Magento\Framework\Object
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
