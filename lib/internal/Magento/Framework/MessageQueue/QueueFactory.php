<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\Queuenterface
 *
 * @api
 * @since 102.0.1
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
     * @since 102.0.1
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
     * {@inheritdoc}
     * @since 102.0.1
     */
    public function create($queueName, $connectionName)
    {
        $connectionType = $this->connectionTypeResolver->getConnectionType($connectionName);
        if (!isset($this->queueFactories[$connectionType])) {
            throw new \LogicException("Not found queue for connection name '{$connectionName}' in config");
        }
        $factory = $this->queueFactories[$connectionType];
        $queue = $factory->create($queueName, $connectionName);

        if (!$queue instanceof QueueInterface) {
            $queueInterface = \Magento\Framework\MessageQueue\QueueInterface::class;
            throw new \LogicException(
                "Queue for connection name '{$connectionName}' does not implement interface '{$queueInterface}'"
            );
        }
        return $queue;
    }
}
