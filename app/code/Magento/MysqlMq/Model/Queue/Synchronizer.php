<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Model\Queue;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MessageQueue\Topology\ConfigInterface;
use Magento\Framework\MessageQueue\Topology\SynchronizerInterface;

class Synchronizer implements SynchronizerInterface
{
    private const QUEUE_TABLE = 'queue';

    /**
     * @param ConfigInterface $topologyConfig
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ConfigInterface $topologyConfig,
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * @inheritdoc
     */
    public function synchronize(): array
    {
        $queues = [];
        foreach ($this->topologyConfig->getQueues() as $queue) {
            $queues[] = $queue->getName();
        }

        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName(self::QUEUE_TABLE);

        $connection->startSetup();
        try {
            $existingQueues = $connection->fetchCol($connection->select()->from($table, 'name'));
            $missingQueues = array_values(array_unique(array_diff($queues, $existingQueues)));
            if (!empty($missingQueues)) {
                $connection->insertArray($table, ['name'], $missingQueues);
            }
        } finally {
            $connection->endSetup();
        }

        return array_map(
            static fn (string $name): string => sprintf('Created queue "%s".', $name),
            $missingQueues
        );
    }
}
