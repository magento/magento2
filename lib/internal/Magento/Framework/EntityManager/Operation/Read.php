<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @return object
     * @throws \Exception
     */
    public function execute($entityType, $entity, $identifier)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $eventPrefix = strtolower(str_replace("\\", "_", $entityType));
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $entity = $this->readMain->execute($entityType, $entity, $identifier);
        $this->eventManager->dispatch(
            'entity_manager_load_before',
            [
                'entity_type' => $entityType,
                'identifier' => $identifier
            ]
        );
        $this->eventManager->dispatchEntityEvent($entityType, 'load_before', ['identifier' => $identifier]);
        $entityData = $hydrator->extract($entity);
        if (isset($entityData[$metadata->getLinkField()])) {
            $entity = $this->readAttributes->execute($entityType, $entity);
            $entity = $this->readExtensions->execute($entityType, $entity);
        }
        $this->eventManager->dispatchEntityEvent($entityType, 'load_after', ['entity' => $entity]);
        $this->eventManager->dispatch(
            'entity_manager_load_after',
            [
                'entity_type' => $entityType,
                'entity' => $entity
            ]
        );

        return $entity;
    }
}
