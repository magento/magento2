<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;

/**
 * Class EntityManager
 */
class EntityManager
{
    /**
     * @var OperationPool
     */
    private $operationPool;

    /**
     * @var CallbackHandler
     */
    private $callbackHandler;

    /**
     * EntityManager constructor.
     *
     * @param OperationPool $operationPool
     * @param MetadataPool $metadataPool
     * @param CallbackHandler $callbackHandler
     */
    public function __construct(
        OperationPool $operationPool,
        MetadataPool $metadataPool,
        CallbackHandler $callbackHandler
    ) {
        $this->operationPool = $operationPool;
        $this->metadataPool = $metadataPool;
        $this->callbackHandler = $callbackHandler;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param string $identifier
     * @return object
     * @throws \Exception
     */
    public function load($entityType, $entity, $identifier)
    {
        $operation = $this->operationPool->getOperation($entityType, 'read');
        $entity = $operation->execute($entityType, $entity, $identifier);
        return $entity;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return bool|object
     * @throws \Exception
     */
    public function save($entityType, $entity)
    {
        if ($this->has($entityType, $entity)) {
            $operation = $this->operationPool->getOperation($entityType, 'update');
        } else {
            $operation = $this->operationPool->getOperation($entityType, 'create');
        }
        try {
        $entity = $operation->execute($entityType, $entity);
            $this->callbackHandler->process($entityType);
        } catch (\Exception $e) {
            $this->callbackHandler->clear($entityType);
            throw $e;
        }
        return $entity;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return bool
     */
    public function has($entityType, $entity)
    {
        $operation = $this->operationPool->getOperation($entityType, 'checkIsExists');
        return $operation->execute($entityType, $entity);
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return bool|object
     * @throws \Exception
     */
    public function delete($entityType, $entity)
    {
        $operation = $this->operationPool->getOperation($entityType, 'delete');
        try {
            $result = $operation->execute($entityType, $entity);
            $this->callbackHandler->process($entityType);
        } catch (\Exception $e) {
            $this->callbackHandler->clear($entityType);
            throw new $e;
        }
        return $result;
    }
}
