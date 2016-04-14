<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

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
     * @param object $entity
     * @param string $identifier
     * @param string|null $entityType
     * @param array $arguments
     * @return mixed
     */
    public function load($entity, $identifier, $entityType = null, $arguments = [])
    {
        $operation = $this->operationPool->getOperation($entityType, 'read');
        $entity = $operation->execute($entityType, $entity, $identifier, $arguments);
        return $entity;
    }

    /**
     * @param object $entity
     * @param string|null $entityType
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function save($entity, $entityType = null, $arguments = [])
    {
        //@todo add EntityTypeResolver
        if ($this->has($entity, $entityType, $arguments)) {
            $operation = $this->operationPool->getOperation($entityType, 'update');
        } else {
            $operation = $this->operationPool->getOperation($entityType, 'create');
        }
        try {
            $entity = $operation->execute($entityType, $entity, $arguments);
            $this->callbackHandler->process($entityType);
        } catch (\Exception $e) {
            $this->callbackHandler->clear($entityType);
            throw $e;
        }
        return $entity;
    }

    /**
     * @param object $entity
     * @param string|null $entityType
     * @param array $arguments
     * @return bool
     */
    public function has($entity, $entityType = null, $arguments = [])
    {
        $operation = $this->operationPool->getOperation($entityType, 'checkIsExists');
        return $operation->execute($entityType, $entity, $arguments);
    }

    /**
     * @param object $entity
     * @param string|null $entityType
     * @param array $arguments
     * @return bool
     */
    public function delete($entity, $entityType = null, $arguments = [])
    {
        $operation = $this->operationPool->getOperation($entityType, 'delete');
        try {
            $result = $operation->execute($entityType, $entity, $arguments);
            $this->callbackHandler->process($entityType);
        } catch (\Exception $e) {
            $this->callbackHandler->clear($entityType);
            throw new $e;
        }
        return $result;
    }
}
