<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Inventory\Model\ResourceModel\Reservation\ReservationQuantity;
use Magento\Inventory\Model\ResourceModel\Stock\StockItemQuantity;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;

/**
 * Return Quantity of products available to be sold by Product SKU and Stock Id
 *
 * @see \Magento\InventoryApi\Api\GetProductQuantityInStockInterface
 * @api
 */
class GetProductQuantityInStock implements GetProductQuantityInStockInterface
{
    /**
     * GetProductQuantityInStock constructor.
     *
     * @param StockItemQuantity $stockItemQty
     * @param ReservationQuantity $reservationQty
     */
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
