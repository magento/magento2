<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Magento\Framework\Model\ResourceModel\Db;

/**
 * @codeCoverageIgnore
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resources;

    /**
     * @var TransactionManagerInterface
     */
    protected $transactionManager;

    /**
     * @var ObjectRelationProcessor
     */
    protected $objectRelationProcessor;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param TransactionManagerInterface $transactionManager
     * @param ObjectRelationProcessor $objectRelationProcessor
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        TransactionManagerInterface $transactionManager,
        ObjectRelationProcessor $objectRelationProcessor
    ) {
        $this->transactionManager = $transactionManager;
        $this->resources = $resource;
        $this->objectRelationProcessor = $objectRelationProcessor;
    }

    /**
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @return TransactionManagerInterface
     */
    public function getTransactionManager()
    {
        return $this->transactionManager;
    }

    /**
     * @return ObjectRelationProcessor
     */
    public function getObjectRelationProcessor()
    {
        return $this->objectRelationProcessor;
    }
}
