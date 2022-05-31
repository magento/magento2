<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Intended to prevent race conditions during order update by concurrent requests.
 */
class OrderMutex implements OrderMutexInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $orderId, callable $callable, array $args = [])
    {
        $connection = $this->resourceConnection->getConnection('sales');
        $connection->beginTransaction();
        $query = $connection->select()
            ->from($this->resourceConnection->getTableName('sales_order'), 'entity_id')
            ->where('entity_id = ?', $orderId)
            ->forUpdate(true);
        $connection->query($query);

        try {
            $result = $callable(...$args);
            $connection->commit();
            return $result;
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
