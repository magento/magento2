<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\InventoryApi\Api\IsProductInStockInterface;

/**
 * Return product availability by Product SKU and Stock Id (stock data + reservations)
 */
class IsProductInStock implements IsProductInStockInterface
{
    /**
     * @var GetStockItemQuantityInterface
     */
    private $getStockItemQuantity;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $getReservationsQuantity;

    /**
     * @param GetStockItemQuantityInterface $getStockItemQuantity
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     */
    public function __construct(
        GetStockItemQuantityInterface $getStockItemQuantity,
        GetReservationsQuantityInterface $getReservationsQuantity
    ) {
        $this->getStockItemQuantity = $getStockItemQuantity;
        $this->getReservationsQuantity = $getReservationsQuantity;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        $productQtyInStock = $this->getStockItemQuantity->execute($sku, $stockId) +
            $this->getReservationsQuantity->execute($sku, $stockId);
        return $productQtyInStock > 0;
    }
}
