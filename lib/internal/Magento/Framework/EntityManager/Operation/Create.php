<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\Framework\EntityManager\Sequence\SequenceApplier;
use Magento\Framework\EntityManager\Operation\Create\CreateMain;
use Magento\Framework\EntityManager\Operation\Create\CreateAttributes;
use Magento\Framework\EntityManager\Operation\Create\CreateExtensions;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Phrase;

/**
 * Class Create
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.1.0
 */
class Create implements CreateInterface
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
     * @var CreateMain
     * @since 2.1.0
     */
    private $createMain;

    /**
     * @var CreateAttributes
     * @since 2.1.0
     */
    private $createAttributes;

    /**
     * @var CreateExtensions
     * @since 2.1.0
     */
    private $createExtensions;

    /**
     * @var SequenceApplier
     * @since 2.2.0
     */
    private $sequenceApplier;

    /**
     * @param MetadataPool $metadataPool
     * @param TypeResolver $typeResolver
     * @param ResourceConnection $resourceConnection
     * @param EventManager $eventManager
     * @param CreateMain $createMain
     * @param CreateAttributes $createAttributes
     * @param CreateExtensions $createExtensions
     * @since 2.1.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        TypeResolver $typeResolver,
        ResourceConnection $resourceConnection,
        EventManager $eventManager,
        CreateMain $createMain,
        CreateAttributes $createAttributes,
        CreateExtensions $createExtensions
    ) {
        $this->metadataPool = $metadataPool;
        $this->typeResolver = $typeResolver;
        $this->resourceConnection = $resourceConnection;
        $this->eventManager = $eventManager;
        $this->createMain = $createMain;
        $this->createAttributes = $createAttributes;
        $this->createExtensions = $createExtensions;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     * @throws AlreadyExistsException
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

            $entity = $this->getSequenceApplier()->apply($entity);

            $entity = $this->createMain->execute($entity, $arguments);
            $entity = $this->createAttributes->execute($entity, $arguments);
            $entity = $this->createExtensions->execute($entity, $arguments);
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

    /**
     * @return SequenceApplier
     *
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getSequenceApplier()
    {
        if (!$this->sequenceApplier) {
            $this->sequenceApplier = ObjectManager::getInstance()->get(
                SequenceApplier::class
            );
        }

        return $this->sequenceApplier;
    }
}
