<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write;

use Magento\Framework\EntityManager\Operation\Write\Create\ValidateCreate;
use Magento\Framework\EntityManager\Operation\Write\Create\CreateMain;
use Magento\Framework\EntityManager\Operation\Write\Create\CreateAttributes;
use Magento\Framework\EntityManager\Operation\Write\Create\CreateExtensions;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class Create
 */
class Create
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
     * @var ValidateCreate
     */
    private $validateCreate;

    /**
     * @var CreateMain
     */
    private $createMain;

    /**
     * @var CreateAttributes
     */
    private $createAttributes;

    /**
     * @var CreateExtensions
     */
    private $createExtensions;

    /**
     * Create constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param ManagerInterface $eventManager
     * @param ValidateCreate $validateCreate
     * @param CreateMain $createMain
     * @param CreateAttributes $createAttributes
     * @param CreateExtensions $createExtensions
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        ManagerInterface $eventManager,
        ValidateCreate $validateCreate,
        CreateMain $createMain,
        CreateAttributes $createAttributes,
        CreateExtensions $createExtensions
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->eventManager = $eventManager;
        $this->validateCreate = $validateCreate;
        $this->createMain = $createMain;
        $this->createAttributes = $createAttributes;
        $this->createExtensions = $createExtensions;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @throws \Exception
     */
    public function execute($entityType, $entity)
    {
        $this->validateCreate->execute($entityType, $entity);
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        $connection->beginTransaction();
        try {
            $this->eventManager->dispatch('entity_save_before', ['entity_type' => $entityType, 'entity' => $entity]);
            $entity = $this->createMain->execute($entityType, $entity);
            $entity = $this->createAttributes->execute($entityType, $entity);
            $entity = $this->createExtensions->execute($entityType, $entity);
            $this->eventManager->dispatch('entity_save_after', ['entity_type' => $entityType, 'entity' => $entity]);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $entity;
    }
}
