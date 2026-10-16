<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Model\QueueConfig;

use Magento\Framework\MessageQueue\Topology\Config\QueueConfigChangeDetectorInterface;
use Magento\MysqlMq\Model\Queue\QueueConfigSynchronizer;

/**
 * Detects missing MySQL MQ queues relative to topology configuration.
 */
class ChangeDetector implements QueueConfigChangeDetectorInterface
{
    /**
     * @param QueueConfigSynchronizer $queueConfigSynchronizer
     */
    public function __construct(
        private readonly QueueConfigSynchronizer $queueConfigSynchronizer
    ) {
    }

    /**
     * @inheritdoc
     */
    public function hasChanges(): bool
    {
        return $this->getMissingQueues() !== [];
    }

    /**
     * @inheritdoc
     */
    public function getMissingQueues(): array
    {
        return $this->queueConfigSynchronizer->getMissingNames();
    }
}
