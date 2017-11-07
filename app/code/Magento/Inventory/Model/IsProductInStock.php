<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Inventory\Model\ResourceModel\Reservation\ReservationQuantity;
use Magento\Inventory\Model\ResourceModel\Stock\StockItemQuantity;
use Magento\InventoryApi\Api\IsProductInStockInterface;

/**
 * Return product availability by Product SKU and Stock Id.
 *
 * @see \Magento\InventoryApi\Api\GetProductQuantityInStockInterface
 * @see \Magento\Inventory\Model\Stock\Command\IsInStockInterface
 */
class IsProductInStock implements IsProductInStockInterface
{
    /**
     * @var StockItemQuantity
     */
    private $stockItemQty;

    /**
     * @var ReservationQuantity
     */
    private $reservationQty;

    /**
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
    public function execute(string $sku, int $stockId): bool
    {
        $productQtyInStock = $this->stockItemQty->execute($sku, $stockId) +
            $this->reservationQty->execute($sku, $stockId);
        return $productQtyInStock > 0;
    }
}
