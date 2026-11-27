<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Model\Queue;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfigInterface;

/**
 * Compares topology-configured queue names with the MySQL MQ queue table.
 */
class QueueConfigSynchronizer
{
    /**
     * @param TopologyConfigInterface $topologyConfig
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly TopologyConfigInterface $topologyConfig,
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * Queue names declared by topology configuration.
     *
     * @return string[]
     */
    public function getConfiguredNames(): array
    {
        $queues = [];
        foreach ($this->topologyConfig->getQueues() as $queue) {
            $queues[] = $queue->getName();
        }

        return array_values(array_unique($queues));
    }

    /**
     * Queue names currently stored in the database.
     *
     * @return string[]
     */
    public function getDatabaseNames(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('queue');
        $select = $connection->select()->from($tableName, ['name']);

        return $connection->fetchCol($select);
    }

    /**
     * Configured queue names that are not yet present in the database.
     *
     * @return string[]
     */
    public function getMissingNames(): array
    {
        return array_values(
            array_unique(
                array_diff($this->getConfiguredNames(), $this->getDatabaseNames())
            )
        );
    }
}
