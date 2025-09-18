<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Bulk\Queue;

/**
 * Queue factory
 */
class QueueRepository
{
    /**
     * @var QueueInterface[]
     */
    private $queueInstances;

    /**
     * @var QueueFactoryInterface
     */
    private $queueFactory;

    /**
     * @param QueueFactoryInterface $queueFactory
     */
    public function __construct(QueueFactoryInterface $queueFactory)
    {
        $this->queueFactory = $queueFactory;
    }

    /**
     * Get queue instance by connection name and queue name.
     *
     * @param string $connectionName
     * @param string $queueName
     * @return QueueInterface
     * @throws \LogicException
     */
    public function get(string $connectionName, string $queueName): QueueInterface
    {
        if (!isset($this->queueInstances[$connectionName][$queueName])) {
            $queue = $this->queueFactory->create($queueName, $connectionName);
            $this->queueInstances[$connectionName][$queueName] = $queue;
        }
        return $this->queueInstances[$connectionName][$queueName];
    }
}
