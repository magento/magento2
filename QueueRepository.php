<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Queue factory
 * @since 2.0.0
 */
class QueueRepository
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @var QueueInterface[]
     * @since 2.0.0
     */
    private $queueInstances;

    /**
     * @var QueueFactoryInterface
     * @since 2.2.0
     */
    private $queueFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string[] $queues
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getQueueFactory()
    {
        if ($this->queueFactory === null) {
            $this->queueFactory = $this->objectManager->get(QueueFactoryInterface::class);
        }
        return $this->queueFactory;
    }
}
