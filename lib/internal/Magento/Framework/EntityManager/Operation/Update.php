<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\Framework\EntityManager\Operation\Update\UpdateMain;
use Magento\Framework\EntityManager\Operation\Update\UpdateAttributes;
use Magento\Framework\EntityManager\Operation\Update\UpdateExtensions;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Phrase;

/**
 * Class Update
 * @since 2.1.0
 */
class Update implements UpdateInterface
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
     * @var UpdateMain
     * @since 2.1.0
     */
    private $updateMain;

    /**
     * @var UpdateAttributes
     * @since 2.1.0
     */
    private $updateAttributes;

    /**
     * @var UpdateExtensions
     * @since 2.1.0
     */
    private $updateExtensions;

    /**
     * @param MetadataPool $metadataPool
     * @param TypeResolver $typeResolver
     * @param ResourceConnection $resourceConnection
     * @param EventManager $eventManager
     * @param UpdateMain $updateMain
     * @param UpdateAttributes $updateAttributes
     * @param UpdateExtensions $updateExtensions
     * @since 2.1.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        TypeResolver $typeResolver,
        ResourceConnection $resourceConnection,
        EventManager $eventManager,
        UpdateMain $updateMain,
        UpdateAttributes $updateAttributes,
        UpdateExtensions $updateExtensions
    ) {
        $this->metadataPool = $metadataPool;
        $this->typeResolver = $typeResolver;
        $this->resourceConnection = $resourceConnection;
        $this->eventManager = $eventManager;
        $this->updateMain = $updateMain;
        $this->updateAttributes = $updateAttributes;
        $this->updateExtensions = $updateExtensions;
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
        $connection->beginTransaction();
        try {
            $this->eventManager->dispatch(
                'entity_manager_save_before',
                [
                    'entity_type' => $entityType,
                    'entity' => $entity
                ]
            );
            $this->eventManager->dispatchEntityEvent($entityType, 'save_before', ['entity' => $entity]);
            $entity = $this->updateMain->execute($entity, $arguments);
            $entity = $this->updateAttributes->execute($entity, $arguments);
            $entity = $this->updateExtensions->execute($entity, $arguments);
            $this->eventManager->dispatchEntityEvent($entityType, 'save_after', ['entity' => $entity]);
            $this->eventManager->dispatch(
                'entity_manager_save_after',
                [
                    'entity_type' => $entityType,
                    'entity' => $entity
                ]
            );
            $connection->commit();
        } catch (DuplicateException $e) {
            $connection->rollBack();
            throw new AlreadyExistsException(new Phrase('Unique constraint violation found'), $e);
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $entity;
    }
}
