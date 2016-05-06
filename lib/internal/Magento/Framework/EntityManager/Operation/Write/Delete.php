<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write;

use Magento\Framework\EntityManager\Operation\Write\Delete\DeleteMain;
use Magento\Framework\EntityManager\Operation\Write\Delete\DeleteAttributes;
use Magento\Framework\EntityManager\Operation\Write\Delete\DeleteExtensions;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;

/**
 * Class Delete
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Delete
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
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var TransactionManagerInterface
     */
    private $transactionManager;

    /**
     * @var DeleteMain
     */
    private $deleteMain;

    /**
     * @var DeleteAttributes
     */
    private $deleteAttributes;

    /**
     * @var DeleteExtensions
     */
    private $deleteExtensions;

    /**
     * Delete constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param EventManager $eventManager
     * @param TransactionManagerInterface $transactionManager
     * @param DeleteMain $deleteMain
     * @param DeleteAttributes $deleteAttributes
     * @param DeleteExtensions $deleteExtensions
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        EventManager $eventManager,
        TransactionManagerInterface $transactionManager,
        DeleteMain $deleteMain,
        DeleteAttributes $deleteAttributes,
        DeleteExtensions $deleteExtensions
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->eventManager = $eventManager;
        $this->transactionManager = $transactionManager;
        $this->deleteMain = $deleteMain;
        $this->deleteAttributes = $deleteAttributes;
        $this->deleteExtensions = $deleteExtensions;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entityType, $entity, $arguments = [])
    {
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
            $entity = $this->deleteExtensions->execute($entityType, $entity, $arguments);
            $entity = $this->deleteAttributes->execute($entityType, $entity, $arguments);
            $entity = $this->deleteMain->execute($entityType, $entity, $arguments);
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
