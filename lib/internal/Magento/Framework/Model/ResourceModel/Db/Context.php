<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db;

/**
 * Constructor modification point for Magento\Framework\Model\ResourceModel\Db\AbstractDb.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    protected $resources;

    /**
     * @var TransactionManagerInterface
     * @since 2.0.0
     */
    protected $transactionManager;

    /**
     * @var ObjectRelationProcessor
     * @since 2.0.0
     */
    protected $objectRelationProcessor;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param TransactionManagerInterface $transactionManager
     * @param ObjectRelationProcessor $objectRelationProcessor
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @return TransactionManagerInterface
     * @since 2.0.0
     */
    public function getTransactionManager()
    {
        return $this->transactionManager;
    }

    /**
     * @return ObjectRelationProcessor
     * @since 2.0.0
     */
    public function getObjectRelationProcessor()
    {
        return $this->objectRelationProcessor;
    }
}
