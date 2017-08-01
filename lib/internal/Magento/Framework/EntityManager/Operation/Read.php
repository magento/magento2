<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\EntityManager\Operation\Read\ReadMain;
use Magento\Framework\EntityManager\Operation\Read\ReadAttributes;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\EntityManager\TypeResolver;

/**
 * Class Read
 * @since 2.1.0
 */
class Read implements ReadInterface
{
    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    private $metadataPool;

    /**
     * @var HydratorPool
     * @since 2.1.0
     */
    private $hydratorPool;

    /**
     * @var EventManager
     * @since 2.1.0
     */
    private $eventManager;

    /**
     * @var TypeResolver
     * @since 2.1.0
     */
    private $typeResolver;

    /**
     * @var ReadMain
     * @since 2.1.0
     */
    private $readMain;

    /**
     * @var ReadAttributes
     * @since 2.1.0
     */
    private $readAttributes;

    /**
     * @var ReadAttributes
     * @since 2.1.0
     */
    private $readExtensions;

    /**
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param TypeResolver $typeResolver
     * @param EventManager $eventManager
     * @param ReadMain $readMain
     * @param ReadAttributes $readAttributes
     * @param ReadExtensions $readExtensions
     * @since 2.1.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        HydratorPool $hydratorPool,
        TypeResolver $typeResolver,
        EventManager $eventManager,
        ReadMain $readMain,
        ReadAttributes $readAttributes,
        ReadExtensions $readExtensions
    ) {
        $this->metadataPool = $metadataPool;
        $this->hydratorPool = $hydratorPool;
        $this->typeResolver = $typeResolver;
        $this->eventManager = $eventManager;
        $this->readMain = $readMain;
        $this->readAttributes = $readAttributes;
        $this->readExtensions = $readExtensions;
    }

    /**
     * {@inheritDoc}
     * @since 2.1.0
     */
    public function execute($entity, $identifier, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
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
                'entity' => $entity,
                'arguments' => $arguments
            ]
        );
        $entity = $this->readMain->execute($entity, $identifier);
        $entityData = array_merge($hydrator->extract($entity), $arguments);
        if (isset($entityData[$metadata->getLinkField()])) {
            $entity = $this->readAttributes->execute($entity, $arguments);
            $entity = $this->readExtensions->execute($entity, $arguments);
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
