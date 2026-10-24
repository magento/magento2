<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order;

/**
 * Sales order status change history resource model.
 */
class SalesOrderStatusChangeHistory
{
    /**
     * Sales order status change log table to store order status and timestamp
     */
    private const TABLE_NAME = 'sales_order_status_change_history';

    /**
     * Sales order table
     */
    private const ORDER_TABLE_NAME = 'sales_order';

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
    ) {
    }

    /**
     * Fetch recent row from table if entry exists against the order
     *
     * @param int $orderId
     * @return array|null
     */
    public function getLatestStatus(int $orderId): ?array
    {
        $connection = $this->resourceConnection->getConnection();
        return $connection->fetchRow(
            $connection->select()->from(
                $this->resourceConnection->getTableName(self::TABLE_NAME),
                ['status', 'created_at']
            )->where(
                'order_id = ?',
                $orderId
            )->order('created_at DESC')
        ) ?: null;
    }

    /**
     * Insert updated status against an order into the table
     *
     * @param Order $order
     * @return void
     */
    public function insert(Order $order): void
    {
        if (!$this->isOrderExists((int)$order->getId()) || $order->getStatus() === null) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->insert(
            $this->resourceConnection->getTableName(self::TABLE_NAME),
            [
                'order_id' => (int)$order->getId(),
                'status' => $order->getStatus()
            ]
        );
    }

    /**
     * Check if order exists in db or is deleted
     *
     * @param int $orderId
     * @return bool
     */
    private function isOrderExists(int $orderId): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $entityId = $connection->fetchOne(
            $connection->select()->from(
                $this->resourceConnection->getTableName(self::ORDER_TABLE_NAME),
                ['entity_id']
            )->where(
                'entity_id = ?',
                $orderId
            )
        );
        return (int) $entityId === $orderId;
    }
}
