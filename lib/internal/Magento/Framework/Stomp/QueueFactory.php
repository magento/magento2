<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

/**
 * Factory class for @see \Magento\Framework\Stomp\Queue
 *
 * @api
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
        $instanceName = Queue::class
    ) {
        $this->objectManager = $objectManager;
        $this->configPool = $configPool;
        $this->instanceName = $instanceName;
    }

    /**
     * @inheritdoc
     */
    public function create($queueName, $connectionName)
    {
        return $this->objectManager->create(
            $this->instanceName,
            [
                'stompConfig' => $this->configPool->get($connectionName),
                'queueName' => $queueName
            ]
        );
    }
}
