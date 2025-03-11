<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellation\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Sales order cancel confirmation resource model.
 */
class SalesOrderConfirmCancel
{
    /**
     * Sales order cancellation table to store confirmation key and reason for guest order
     */
    private const TABLE_NAME = 'sales_order_confirm_cancel';

    /**
     * SalesOrderConfirmCancel Constructor
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * Fetch row from table if entry exists against the order
     *
     * @param int $orderId
     * @return array|null
     */
    public function get(int $orderId): ?array
    {
        $connection = $this->resourceConnection->getConnection();
        return $connection->fetchRow(
            $connection->select()->from(
                $this->resourceConnection->getTableName(self::TABLE_NAME)
            )->where(
                'order_id = ?',
                $orderId
            )
        ) ?: [];
    }

    /**
     * Insert confirmation key & cancellation reason against an order into the table
     *
     * @param int $orderId
     * @param string $confirmationKey
     * @param string $reason
     * @return void
     */
    public function insert(int $orderId, string $confirmationKey, string $reason): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->insertOnDuplicate(
            $this->resourceConnection->getTableName(self::TABLE_NAME),
            [
                'order_id' => $orderId,
                'confirmation_key' => $confirmationKey,
                'reason' => $reason
            ],
            ['confirmation_key', 'reason']
        );
    }
}
