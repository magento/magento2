<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Queue factory
 */
class QueueRepository
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var QueueInterface[]
     */
    private $queueInstances;

    /**
     * @var QueueFactoryInterface
     */
    private $queueFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string[] $queues
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $queues = [])
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get queue instance by connection name and queue name.
     *
     * @param string $connectionName
     * @param string $queueName
     * @return QueueInterface
     * @throws \LogicException
     */
    public function get($connectionName, $queueName)
    {
        if (!isset($this->queueInstances[$connectionName][$queueName])) {
            $queue = $this->getQueueFactory()->create($queueName, $connectionName);
            $this->queueInstances[$connectionName][$queueName] = $queue;
        }
        return $this->queueInstances[$connectionName][$queueName];
    }

    /**
     * Get queue factory.
     *
     * @return QueueFactoryInterface
     * @deprecated 102.0.2
     */
    private function getQueueFactory()
    {
        if ($this->queueFactory === null) {
            $this->queueFactory = $this->objectManager->get(QueueFactoryInterface::class);
        }
        return $this->queueFactory;
    }
}
