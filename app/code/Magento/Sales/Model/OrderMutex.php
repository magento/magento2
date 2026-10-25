<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\DeadlockRecoveryExecutorInterface;

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
     * @var DeadlockRecoveryExecutorInterface
     */
    private $deadlockRecoveryExecutor;
    /**
     * @param ResourceConnection $resourceConnection
     * @param DeadlockRecoveryExecutorInterface $deadlockRecoveryExecutor
     */

    public function __construct(
        ResourceConnection $resourceConnection,
        DeadlockRecoveryExecutorInterface $deadlockRecoveryExecutor
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->deadlockRecoveryExecutor = $deadlockRecoveryExecutor;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $orderId, callable $callable, array $args = [])
    {
        $connection = $this->resourceConnection->getConnection('sales');
        return $this->deadlockRecoveryExecutor->execute(
            $connection,
            \Closure::fromCallable([$this, 'updateOrder']),
            [$connection, $orderId, $callable, $args]
        );
    }

    /**
     * Executes callable
     *
     * @param AdapterInterface $connection
     * @param int $orderId
     * @param callable $callable
     * @param array $args
     * @return mixed
     * @throws \Throwable
     */
    private function updateOrder(AdapterInterface $connection, int $orderId, callable $callable, array $args)
    {
        $query = $connection->select()
            ->from($this->resourceConnection->getTableName('sales_order'), 'entity_id')
            ->where('entity_id = ?', $orderId)
            ->forUpdate(true);
        $connection->query($query);

        $result = $callable(...$args);

        return $result;
    }
}
