<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\Config\Data as QueueConfig;

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
     * @var string[]
     */
    private $queues;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string[] $queues
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $queues)
    {
        $this->objectManager = $objectManager;
        $this->queues = $queues;
    }

    /**
     * @param string $connectionName
     * @return QueueInterface
     */
    public function getByQueueName($connectionName)
    {
        if (!isset($this->queues[$connectionName])) {
            throw new \LogicException("Not found queue for connection name '{$connectionName}' in config");
        }

        $queueClassName = $this->queues[$connectionName];
        $queue = $this->objectManager->get($queueClassName);

        if (!$queue instanceof QueueInterface) {
            $queueInterface = '\Magento\Framework\Amqp\QueueInterface';
            throw new \LogicException("Queue '{$queueClassName}' for connection name '{$connectionName}' " .
                "does not implement interface '{$queueInterface}'");
        }

        return $queue;
    }
}
