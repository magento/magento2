<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\MessageQueue\QueueFactoryInterface;
use Magento\Framework\Stomp\Exception\ClientException;

/**
 * Factory class for @see \Magento\Framework\Stomp\Queue
 *
 * @api
 */
class QueueFactory implements QueueFactoryInterface
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
     * @var StompClientProvider
     */
    private StompClientProvider $stompClientProvider;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigPool $configPool
     * @param string $instanceName
     * @param StompClientProvider|null $stompClientProvider
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ConfigPool $configPool,
        $instanceName = Queue::class,
        ?StompClientProvider $stompClientProvider = null,
    ) {
        $this->objectManager = $objectManager;
        $this->configPool = $configPool;
        $this->instanceName = $instanceName;
        $this->stompClientProvider = $stompClientProvider ?? ObjectManager::getInstance()
            ->get(StompClientProvider::class);
    }

    /**
     * @inheritdoc
     */
    public function create($queueName, $connectionName)
    {
        $stompClient = $this->stompClientProvider->get($connectionName);

        return $this->objectManager->create(
            $this->instanceName,
            [
                'stompConfig' => $this->configPool->get($connectionName),
                'stompClient' => $stompClient,
                'queueName' => $queueName
            ]
        );
    }
}
