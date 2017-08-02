<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Type\Db;

use Magento\Framework\ObjectManagerInterface;

/**
 * Connection adapter factory
 * @since 2.0.0
 */
class ConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function create(array $connectionConfig)
    {
        /** @var \Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface $adapterInstance */
        $adapterInstance = $this->objectManager->create(
            \Magento\Framework\App\ResourceConnection\ConnectionAdapterInterface::class,
            ['config' => $connectionConfig]
        );

        return $adapterInstance->getConnection();
    }
}
