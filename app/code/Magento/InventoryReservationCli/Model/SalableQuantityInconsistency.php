<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Filter orders for missing initial reservation
 */
class SalableQuantityInconsistency
{
    /**
     * @var OrderInterface
     */
    private $order;

    /**+
     * @var int
     */
    private $objectId;

    /**+
     * @var int
     */
    private $stockId;

    /**
     * List of SKUs and quantity
     * @var array
     */
    private $items = [];

    /**
     * @return OrderInterface|null
     */
    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    /**
     * @param OrderInterface $order
     */
    public function setOrder(OrderInterface $order): void
    {
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getObjectId(): int
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     */
    public function setObjectId(int $objectId): void
    {
        $this->objectId = $objectId;
    }

    /**
     * @param string $sku
     * @param float $qty
     */
    public function addItemQty(string $sku, float $qty): void
    {
        if (!isset($this->items[$sku])) {
            $this->items[$sku] = 0.0;
        }
        $this->items[$sku] += $qty;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return int
     */
    public function getStockId(): int
    {
        return $this->stockId;
    }

    /**
     * @param int $stockId
     */
    public function setStockId(int $stockId): void
    {
        $this->stockId = $stockId;
    }
}
