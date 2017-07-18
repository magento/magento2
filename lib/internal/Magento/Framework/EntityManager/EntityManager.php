<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager;

use Magento\Framework\EntityManager\Operation\CheckIfExistsInterface;
use Magento\Framework\EntityManager\Operation\CreateInterface;
use Magento\Framework\EntityManager\Operation\DeleteInterface;
use Magento\Framework\EntityManager\Operation\ReadInterface;
use Magento\Framework\EntityManager\Operation\UpdateInterface;

/**
 * It's not recommended to use EntityManager and its infrastructure for your entities persistence.
 * In the nearest future new Persistence Entity Manager would be released which will cover all the requirements for
 * persistence layer along with Query API as performance efficient APIs for Read scenarios.
 *
 * Currently, it's recommended to use Resource Model infrastructure and make a successor of
 * Magento\Framework\Model\ResourceModel\Db\AbstractDb class or successor of
 * Magento\Eav\Model\Entity\AbstractEntity if EAV attributes support needed.
 *
 * For filtering operations, it's recommended to use successor of
 * Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @param OperationPool $operationPool
     * @param MetadataPool $metadataPool
     * @param TypeResolver $typeResolver
     * @param CallbackHandler $callbackHandler
     */
    public function __construct(
        OperationPool $operationPool,
        MetadataPool $metadataPool,
        TypeResolver $typeResolver,
        CallbackHandler $callbackHandler
    ) {
        $this->operationPool = $operationPool;
        $this->metadataPool = $metadataPool;
        $this->typeResolver = $typeResolver;
        $this->callbackHandler = $callbackHandler;
    }

    /**
     * @param object $entity
     * @param string $identifier
     * @param array $arguments
     * @return mixed
     * @throws \LogicException
     */
    public function load($entity, $identifier, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $operation = $this->operationPool->getOperation($entityType, 'read');
        if (!($operation instanceof ReadInterface)) {
            throw new \LogicException(get_class($operation) . ' must implement ' . ReadInterface::class);
        }
        $entity = $operation->execute($entity, $identifier, $arguments);
        return $entity;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \LogicException
     * @throws \Exception
     */
    public function save($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        if ($this->has($entity)) {
            $operation = $this->operationPool->getOperation($entityType, 'update');
            if (!($operation instanceof UpdateInterface)) {
                throw new \LogicException(get_class($operation) . ' must implement ' . UpdateInterface::class);
            }
        } else {
            $operation = $this->operationPool->getOperation($entityType, 'create');
            if (!($operation instanceof CreateInterface)) {
                throw new \LogicException(get_class($operation) . ' must implement ' . CreateInterface::class);
            }
        }
        try {
            $entity = $operation->execute($entity, $arguments);
            $this->callbackHandler->process($entityType);
        } catch (\Exception $e) {
            $this->callbackHandler->clear($entityType);
            throw $e;
        }
        return $entity;
    }

    /**
     * @param object $entity
     * @return bool
     * @throws \LogicException
     */
    public function has($entity)
    {
        $entityType = $this->typeResolver->resolve($entity);
        $operation = $this->operationPool->getOperation($entityType, 'checkIfExists');
        if (!($operation instanceof CheckIfExistsInterface)) {
            throw new \LogicException(get_class($operation) . ' must implement ' . CheckIfExistsInterface::class);
        }
        return $operation->execute($entity);
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return bool
     * @throws \LogicException
     * @throws \Exception
     */
    public function delete($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $operation = $this->operationPool->getOperation($entityType, 'delete');
        if (!($operation instanceof DeleteInterface)) {
            throw new \LogicException(get_class($operation) . ' must implement ' . DeleteInterface::class);
        }
        try {
            $operation->execute($entity, $arguments);
            $this->callbackHandler->process($entityType);
        } catch (\Exception $e) {
            $this->callbackHandler->clear($entityType);
            throw $e;
        }
        return true;
    }
}
