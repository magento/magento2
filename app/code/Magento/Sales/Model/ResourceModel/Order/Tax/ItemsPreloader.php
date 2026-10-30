<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Tax;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Loads applied tax items of several orders in one query and hands each order's rows out once
 */
class ItemsPreloader implements ResetAfterRequestInterface
{
    /**
     * @var array<int, array>
     */
    private array $itemsByOrderId = [];

    /**
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        private readonly ItemFactory $itemFactory
    ) {
    }

    /**
     * Load tax items of the given orders
     *
     * @param int[] $orderIds
     * @return void
     */
    public function preload(array $orderIds): void
    {
        $orderIds = array_map('intval', $orderIds);
        if (!$orderIds) {
            return;
        }

        $itemsByOrderId = array_fill_keys($orderIds, []);
        foreach ($this->itemFactory->create()->getTaxItemsByOrderIds($orderIds) as $row) {
            $itemsByOrderId[(int)$row['order_id']][] = $row;
        }
        $this->itemsByOrderId = $itemsByOrderId + $this->itemsByOrderId;
    }

    /**
     * Return and forget the preloaded tax items of an order, null when the order was not preloaded
     *
     * @param int $orderId
     * @return array|null
     */
    public function take(int $orderId): ?array
    {
        if (!array_key_exists($orderId, $this->itemsByOrderId)) {
            return null;
        }
        $items = $this->itemsByOrderId[$orderId];
        unset($this->itemsByOrderId[$orderId]);

        return $items;
    }

    /**
     * Forget all preloaded tax items
     *
     * @return void
     */
    public function clear(): void
    {
        $this->itemsByOrderId = [];
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->clear();
    }
}
