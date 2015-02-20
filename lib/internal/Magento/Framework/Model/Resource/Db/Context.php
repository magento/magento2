<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Magento\Framework\Model\Resource\Db;

/**
 * @codeCoverageIgnore
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Framework\App\Resource
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
     * @param \Magento\Framework\App\Resource $resource
     * @param TransactionManagerInterface $transactionManager
     * @param ObjectRelationProcessor $objectRelationProcessor
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        TransactionManagerInterface $transactionManager,
        ObjectRelationProcessor $objectRelationProcessor
    ) {
        $this->transactionManager = $transactionManager;
        $this->resources = $resource;
        $this->objectRelationProcessor = $objectRelationProcessor;
    }

    /**
     * @return \Magento\Framework\App\Resource
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
