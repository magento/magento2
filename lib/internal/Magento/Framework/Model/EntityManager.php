<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Api\SearchCriteria;

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
     * @param OrchestratorPool $orchestratorPool
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        OrchestratorPool $orchestratorPool,
        MetadataPool $metadataPool
    ) {
        $this->orchestratorPool = $orchestratorPool;
        $this->metadataPool = $metadataPool;
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
        $operation = $this->orchestratorPool->getReadOperation($entityType);
        return $operation->execute($entityType, $entity, $identifier);
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
        return $operation->execute($entityType, $entity);
    }

    /**
     * Is entity exists in Entity Manager
     *
     * @param string $entityType
     * @param string $identifier
     * @return bool
     * @throws \Exception
     */
    public function has($entityType, $identifier)
    {
        return $this->metadataPool->getMetadata($entityType)->checkIsEntityExists($identifier);
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return bool|object
     * @throws \Exception
     */
    public function delete($entityType, $entity)
    {
        $operation = $this->orchestratorPool->getWriteOperation($entityType, 'delete');
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
