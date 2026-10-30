<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Model\ResourceModel\Type\Db;

use Magento\Framework\ObjectManagerInterface;

/**
 * Connection adapter factory
 */
class ConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
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
