<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface as TransactionManager;

/**
 * Class EntityManager
 */
class EntityManager
{
    /**
     * @var OrchestratorPool
     */
    private $orchestratorPool;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ObjectRelationProcessor
     */
    private $relationProcessor;

    /**
     * @var EventManager
     */
    private $eventManger;

    /**
     * @var CommitCallback
     */
    private $commitCallback;

    /**
     * @var TransactionManager
     */
    private $transactionManager;

    /**
     * EntityManager constructor.
     *
     * @param OrchestratorPool $orchestratorPool
     * @param MetadataPool $metadataPool
     * @param ObjectRelationProcessor $relationProcessor
     * @param EventManager $eventManager
     * @param CommitCallback $commitCallback
     * @param TransactionManager $transactionManager
     */
    public function __construct(
        OrchestratorPool $orchestratorPool,
        MetadataPool $metadataPool,
        ObjectRelationProcessor $relationProcessor,
        EventManager $eventManager,
        CommitCallback $commitCallback,
        TransactionManager $transactionManager
    ) {
        $this->relationProcessor = $relationProcessor;
        $this->orchestratorPool = $orchestratorPool;
        $this->metadataPool = $metadataPool;
        $this->eventManger = $eventManager;
        $this->commitCallback = $commitCallback;
        $this->transactionManager = $transactionManager;
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
        $this->eventManger->dispatch(
            'entity_load_before',
            [
                'entity_type' => $entityType,
                'identifier' => $identifier
            ]
        );
        $operation = $this->orchestratorPool->getReadOperation($entityType);
        $entity = $operation->execute($entityType, $entity, $identifier);
        $this->eventManger->dispatch(
            'entity_load_after',
            [
                'entity_type' => $entityType,
                'entity' => $entity
            ]
        );
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
        $hydrator = $this->metadataPool->getHydrator($entityType);
        $metadata = $this->metadataPool->getMetadata($entityType);
        $entityData = $hydrator->extract($entity);
        $connection = $metadata->getEntityConnection();

        if (!empty($entityData[$metadata->getIdentifierField()])
            && $metadata->checkIsEntityExists($entityData[$metadata->getIdentifierField()])
        ) {
            $operation = $this->orchestratorPool->getWriteOperation($entityType, 'update');
        } else {
            $operation = $this->orchestratorPool->getWriteOperation($entityType, 'create');
        }
        $connection->beginTransaction();
        try {
            $this->eventManger->dispatch(
                'entity_save_before',
                [
                    'entity_type' => $entityType,
                    'entity' => $entity
                ]
            );
            $this->relationProcessor->validateDataIntegrity($metadata->getEntityTable(), $entityData);
            $entity = $operation->execute($entityType, $entity);
            $this->eventManger->dispatch(
                'entity_save_after',
                [
                    'entity_type' => $entityType,
                    'entity' => $entity
                ]
            );
            $connection->commit();
            $this->commitCallback->process($entityType);
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->commitCallback->clear($entityType);
            throw $e;
        }
        return $entity;
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
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $metadata->getEntityConnection();
        $operation = $this->orchestratorPool->getWriteOperation($entityType, 'delete');
        $this->transactionManager->start($connection);
        try {
            $this->eventManger->dispatch(
                'entity_delete_before',
                [
                    'entity_type' => $entityType,
                    'entity' => $entity
                ]
            );
            $result = $operation->execute($entityType, $entity);
            $this->eventManger->dispatch(
                'entity_delete_after',
                [
                    'entity_type' => $entityType,
                    'entity' => $entity
                ]
            );
            $this->transactionManager->commit();
            $this->commitCallback->process($entityType);
        } catch (\Exception $e) {
            $this->transactionManager->rollBack();
            $this->commitCallback->clear($entityType);
            throw new $e;
        }
        return $result;
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
