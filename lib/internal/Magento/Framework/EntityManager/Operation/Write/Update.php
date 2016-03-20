<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write;

use Magento\Framework\EntityManager\Operation\Write\Update\ValidateUpdate;
use Magento\Framework\EntityManager\Operation\Write\Update\UpdateMain;
use Magento\Framework\EntityManager\Operation\Write\Update\UpdateAttributes;
use Magento\Framework\EntityManager\Operation\Write\Update\UpdateExtensions;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class Update
 */
class Update
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var ValidateUpdate
     */
    private $validateUpdate;

    /**
     * @var UpdateMain
     */
    private $updateMain;

    /**
     * @var UpdateAttributes
     */
    private $updateAttributes;

    /**
     * @var UpdateExtensions
     */
    private $updateExtensions;

    /**
     * Update constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param ManagerInterface $eventManager
     * @param ValidateUpdate $validateUpdate
     * @param UpdateMain $updateMain
     * @param UpdateAttributes $updateAttributes
     * @param UpdateExtensions $updateExtensions
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        ManagerInterface $eventManager,
        ValidateUpdate $validateUpdate,
        UpdateMain $updateMain,
        UpdateAttributes $updateAttributes,
        UpdateExtensions $updateExtensions
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->eventManager = $eventManager;
        $this->validateUpdate = $validateUpdate;
        $this->updateMain = $updateMain;
        $this->updateAttributes = $updateAttributes;
        $this->updateExtensions = $updateExtensions;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @throws \Exception
     */
    public function execute($entityType, $entity)
    {
        $this->validateUpdate->execute($entityType, $entity);
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        $connection->beginTransaction();
        try {
            $this->eventManager->dispatch('entity_save_before', ['entity_type' => $entityType, 'entity' => $entity]);
            $entity = $this->updateMain->execute($entityType, $entity);
            $entity = $this->updateAttributes->execute($entityType, $entity);
            $entity = $this->updateExtensions->execute($entityType, $entity);
            $this->eventManager->dispatch('entity_save_after', ['entity_type' => $entityType, 'entity' => $entity]);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $entity;
    }
}
