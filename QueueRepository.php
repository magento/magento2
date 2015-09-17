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
     * @var QueueInterface[]
     */
    private $queueInstances;

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
     * @param string $queueName
     * @return QueueInterface
     */
    public function get($connectionName, $queueName)
    {
        if (!isset($this->queueInstances[$queueName])) {
            if (!isset($this->queues[$connectionName])) {
                throw new \LogicException("Not found queue for connection name '{$connectionName}' in config");
            }

            $queueClassName = $this->queues[$connectionName];
            $queue = $this->objectManager->create($queueClassName, ['queueName' => $queueName]);

            if (!$queue instanceof QueueInterface) {
                $queueInterface = '\Magento\Framework\Amqp\QueueInterface';
                throw new \LogicException("Queue '{$queueClassName}' for connection name '{$connectionName}' " .
                    "does not implement interface '{$queueInterface}'");
            }

            $this->queueInstances[$queueName] = $queue;
        }

        return $this->queueInstances[$queueName];
    }
}
