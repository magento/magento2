<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * Factory class for @see \Magento\Framework\Amqp\Queue
 *
 * @api
 * @since 100.0.0
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
     * @since 100.0.0
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
     * @since 100.0.0
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
