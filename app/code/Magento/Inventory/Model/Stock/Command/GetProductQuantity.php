<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Stock\Command;

use Magento\Inventory\Model\ResourceModel\Reservation\ReservationQuantity;
use Magento\Inventory\Model\ResourceModel\Stock\StockItemQuantity;

/**
 * Determine whether a product is in stock command (Service Provider Interface - SPI)
 *
 * You can extend and implement this command interface to customize current behaviour, but you are NOT expected to use
 * (call) it in the code of business logic directly.
 *
 * @see \Magento\InventoryApi\Api\IsProductInStockInterface
 * @api
 */
class GetProductQuantity implements GetProductQuantityInterface
{
    /**
     * @var StockItemQuantity
     */
    private $stockItemQty;

    /**
     * @var ReservationQuantity
     */
    private $reservationQty;

    public function __construct(
        StockItemQuantity $stockItemQty,
        ReservationQuantity $reservationQty
    ) {
        $this->stockItemQty = $stockItemQty;
        $this->reservationQty = $reservationQty;
    }


    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): float
    {
        $productQtyInStock = $this->stockItemQty->execute($sku, $stockId) +
            $this->reservationQty->execute($sku, $stockId);
        return (float) $productQtyInStock;
    }
}