<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Driver;

/**
 * Factory class for @see \Magento\MysqlMq\Model\Driver\Queue
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
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\MysqlMq\Model\Driver\Queue::class
    ) {
        $this->objectManager = $objectManager;
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
                'queueName' => $queueName,
                'connectionName' => $connectionName
            ]
        );
    }
}
