<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Operation\Read\ReadMain;
use Magento\Framework\EntityManager\Operation\Read\ReadAttributes;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\EventManager;

/**
 * Class Read
 */
class Read
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var ReadMain
     */
    private $readMain;

    /**
     * @var ReadAttributes
     */
    private $readAttributes;

    /**
     * @var ReadAttributes
     */
    private $readExtensions;

    /**
     * Read constructor
     *
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param EventManager $eventManager
     * @param ReadMain $readMain
     * @param ReadAttributes $readAttributes
     * @param ReadExtensions $readExtensions
     */
    public function __construct(
        MetadataPool $metadataPool,
        HydratorPool $hydratorPool,
        EventManager $eventManager,
        ReadMain $readMain,
        ReadAttributes $readAttributes,
        ReadExtensions $readExtensions
    ) {
        $this->metadataPool = $metadataPool;
        $this->hydratorPool = $hydratorPool;
        $this->eventManager = $eventManager;
        $this->readMain = $readMain;
        $this->readAttributes = $readAttributes;
        $this->readExtensions = $readExtensions;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param string $identifier
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entityType, $entity, $identifier, $arguments = [])
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $this->eventManager->dispatch(
            'entity_manager_load_before',
            [
                'entity_type' => $entityType,
                'identifier' => $identifier,
                'arguments' => $arguments
            ]
        );
        $this->eventManager->dispatchEntityEvent(
            $entityType,
            'load_before',
            [
                'identifier' => $identifier,
                'arguments' => $arguments
            ]
        );
        $entity = $this->readMain->execute($entityType, $entity, $identifier, $arguments);
        $entityData = $hydrator->extract($entity);
        if (isset($entityData[$metadata->getLinkField()])) {
            $entity = $this->readAttributes->execute($entityType, $entity, $arguments);
            $entity = $this->readExtensions->execute($entityType, $entity, $arguments);
        }
        $this->eventManager->dispatchEntityEvent(
            $entityType,
            'load_after',
            [
                'entity' => $entity,
                'arguments' => $arguments
            ]
        );
        $this->eventManager->dispatch(
            'entity_manager_load_after',
            [
                'entity_type' => $entityType,
                'entity' => $entity,
                'arguments' => $arguments
            ]
        );

        return $entity;
    }
}
