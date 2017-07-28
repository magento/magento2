<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Driver;

/**
 * Factory class for @see \Magento\MysqlMq\Model\Driver\Exchange
 * @since 2.2.0
 */
class ExchangeFactory implements \Magento\Framework\MessageQueue\ExchangeFactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     * @since 2.2.0
     */
    private $instanceName = null;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\MysqlMq\Model\Driver\Exchange::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function create($connectionName, array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
