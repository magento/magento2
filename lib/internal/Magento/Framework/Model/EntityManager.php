<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Model\EntityRegistry;

/**
 * Class EntityManager
 */
class EntityManager
{
    /**
     * @var OrchestratorPool
     */
    protected $orchestratorPool;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var EntityRegistry
     */
    protected $entityRegistry;

    /**
     * @param OrchestratorPool $orchestratorPool
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        OrchestratorPool $orchestratorPool,
        MetadataPool $metadataPool,
        EntityRegistry $entityRegistry
    ) {
        $this->orchestratorPool = $orchestratorPool;
        $this->metadataPool = $metadataPool;
        $this->entityRegistry = $entityRegistry;
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
        if (!$this->entityRegistry->retrieve($entityType, $identifier)) {
            $operation = $this->orchestratorPool->getReadOperation($entityType);
            $entity = $operation->execute($entityType, $entity, $identifier);
            $this->entityRegistry->register($entityType, $identifier, $entity);
        }
        return $this->entityRegistry->retrieve($entityType, $identifier);
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return bool|object
     * @throws \Exception
     */
    public function save($entityType, $entity)
    {
        $hydrator = $this->metadataPool->getHydrator($entityType);
        $metadata = $this->metadataPool->getMetadata($entityType);
        $entityData = $hydrator->extract($entity);
        if (!empty($entityData[$metadata->getIdentifierField()])
            && $metadata->checkIsEntityExists($entityData[$metadata->getIdentifierField()])
        ) {
            $operation = $this->orchestratorPool->getWriteOperation($entityType, 'update');
        } else {
            $operation = $this->orchestratorPool->getWriteOperation($entityType, 'create');
        }
        $entity = $operation->execute($entityType, $entity);
        $this->entityRegistry->remove($entityType, $entityData[$metadata->getIdentifierField()]);
        return $entity;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return bool|object
     * @throws \Exception
     */
    public function delete($entityType, $entity)
    {
        $hydrator = $this->metadataPool->getHydrator($entityType);
        $metadata = $this->metadataPool->getMetadata($entityType);
        $entityData = $hydrator->extract($entity);
        if (empty($entityData[$metadata->getIdentifierField()])) {
            return false;
        }
        $identifier = $entityData[$metadata->getIdentifierField()];
        $operation = $this->orchestratorPool->getWriteOperation($entityType, 'delete');
        $this->entityRegistry->remove($entityType, $identifier);
        return $operation->execute($entityType, $entity);
    }

    /**
     * @param string $entityType
     * @param SearchCriteria $searchCriteria
     * @return object[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function find($entityType, SearchCriteria $searchCriteria)
    {
        //TODO:: implement method
    }
}
