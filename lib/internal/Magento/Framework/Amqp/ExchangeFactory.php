<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * Factory class for @see \Magento\Framework\Amqp\Exchange
 *
 * @api
 * @since 102.0.5
 */
class ExchangeFactory implements \Magento\Framework\MessageQueue\ExchangeFactoryInterface
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
        $instanceName = \Magento\Framework\Amqp\Exchange::class
    ) {
        $this->objectManager = $objectManager;
        $this->configPool = $configPool;
        $this->instanceName = $instanceName;
    }

    /**
     * {@inheritdoc}
     * @since 102.0.5
     */
    public function create($connectionName, array $data = [])
    {
        $data['amqpConfig'] = $this->configPool->get($connectionName);
        return $this->objectManager->create(
            $this->instanceName,
            $data
        );
    }
}
