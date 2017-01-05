<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * Factory class for @see \Magento\Framework\Amqp\Queue
 */
class QueueFactory implements \Magento\Framework\MessageQueue\QueueFactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    private $instanceName = null;

    /**
     * @var ConfigPool
     */
    private $configPool;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigPool $configPool
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ConfigPool $configPool,
        $instanceName = \Magento\Framework\Amqp\Queue::class
    ) {
        $this->objectManager = $objectManager;
        $this->configPool = $configPool;
        $this->instanceName = $instanceName;
    }

    /**
     * {@inheritdoc}
     */
    public function create($queueName, $connectionName)
    {
        return $this->objectManager->create(
            $this->instanceName,
            [
                'amqpConfig' => $this->configPool->get($connectionName),
                'queueName' => $queueName
            ]
        );
    }
}
