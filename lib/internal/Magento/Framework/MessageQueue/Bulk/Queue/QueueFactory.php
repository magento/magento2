<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Bulk\Queue;

use Magento\Framework\MessageQueue\ConnectionTypeResolver;

/**
 * Factory class for @see QueueInterface
 *
 * @api
 */
class QueueFactory implements QueueFactoryInterface
{
    /**
     * @var QueueFactoryInterface[]
     */
    private $queueFactories;

    /**
     * @var ConnectionTypeResolver
     */
    private $connectionTypeResolver;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Initialize dependencies.
     *
     * @param ConnectionTypeResolver $connectionTypeResolver
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param QueueFactoryInterface[] $queueFactories
     */
    public function __construct(
        ConnectionTypeResolver $connectionTypeResolver,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $queueFactories = []
    ) {
        $this->objectManager = $objectManager;
        $this->queueFactories = $queueFactories;
        $this->connectionTypeResolver = $connectionTypeResolver;
    }

    /**
     * @inheritdoc
     */
    public function create(string $queueName, string $connectionName): QueueInterface
    {
        $connectionType = $this->connectionTypeResolver->getConnectionType($connectionName);
        if (!isset($this->queueFactories[$connectionType])) {
            throw new \LogicException("Not found queue for connection name '{$connectionName}' in config");
        }
        $factory = $this->queueFactories[$connectionType];
        $queue = $factory->create($queueName, $connectionName);

        if (!$queue instanceof QueueInterface) {
            $queueInterface = QueueInterface::class;
            throw new \LogicException(
                "Queue for connection name '{$connectionName}' does not implement interface '{$queueInterface}'"
            );
        }
        return $queue;
    }
}
