<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\EntityManager\Operation\DeleteInterface;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\EntityManager\Operation\Delete\DeleteMain;
use Magento\Framework\EntityManager\Operation\Delete\DeleteAttributes;
use Magento\Framework\EntityManager\Operation\Delete\DeleteExtensions;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\App\ResourceConnection;

/**
 * Class Delete
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.1.0
 */
class Delete implements DeleteInterface
{
    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    private $metadataPool;

    /**
     * @var TypeResolver
     * @since 2.1.0
     */
    private $typeResolver;

    /**
     * @var ResourceConnection
     * @since 2.1.0
     */
    private $resourceConnection;

    /**
     * @var EventManager
     * @since 2.1.0
     */
    private $eventManager;

    /**
     * @var TransactionManagerInterface
     * @since 2.1.0
     */
    private $transactionManager;

    /**
     * @var DeleteMain
     * @since 2.1.0
     */
    private $deleteMain;

    /**
     * @var DeleteAttributes
     * @since 2.1.0
     */
    private $deleteAttributes;

    /**
     * @var DeleteExtensions
     * @since 2.1.0
     */
    private $deleteExtensions;

    /**
     * @param MetadataPool $metadataPool
     * @param TypeResolver $typeResolver
     * @param ResourceConnection $resourceConnection
     * @param EventManager $eventManager
     * @param TransactionManagerInterface $transactionManager
     * @param DeleteMain $deleteMain
     * @param DeleteAttributes $deleteAttributes
     * @param DeleteExtensions $deleteExtensions
     * @since 2.1.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        TypeResolver $typeResolver,
        ResourceConnection $resourceConnection,
        EventManager $eventManager,
        TransactionManagerInterface $transactionManager,
        DeleteMain $deleteMain,
        DeleteAttributes $deleteAttributes,
        DeleteExtensions $deleteExtensions
    ) {
        $this->metadataPool = $metadataPool;
        $this->typeResolver = $typeResolver;
        $this->resourceConnection = $resourceConnection;
        $this->eventManager = $eventManager;
        $this->transactionManager = $transactionManager;
        $this->deleteMain = $deleteMain;
        $this->deleteAttributes = $deleteAttributes;
        $this->deleteExtensions = $deleteExtensions;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     * @since 2.1.0
     */
    public function execute($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        $this->transactionManager->start($connection);
        try {
            $this->eventManager->dispatch(
                'entity_manager_delete_before',
                [
                    'entity_type' => $entityType,
                    'entity' => $entity
                ]
            );
            $this->eventManager->dispatchEntityEvent($entityType, 'delete_before', ['entity' => $entity]);
            $entity = $this->deleteExtensions->execute($entity, $arguments);
            $entity = $this->deleteAttributes->execute($entity, $arguments);
            $entity = $this->deleteMain->execute($entity, $arguments);
            $this->eventManager->dispatchEntityEvent($entityType, 'delete_after', ['entity' => $entity]);
            $this->eventManager->dispatch(
                'entity_manager_delete_before',
                [
                    'entity_type' => $entityType,
                    'entity' => $entity
                ]
            );
            $this->transactionManager->commit();
        } catch (\Exception $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
        return $entity;
    }
}
