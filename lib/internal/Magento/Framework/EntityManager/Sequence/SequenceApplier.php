<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Sequence;

/**
 * Applier of sequence identifier.
 */
class SequenceApplier
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\EntityManager\TypeResolver
     */
    private $typeResolver;

    /**
     * @var \Magento\Framework\EntityManager\Sequence\SequenceManager
     */
    private $sequenceManager;

    /**
     * @var \Magento\Framework\EntityManager\Sequence\SequenceRegistry
     */
    private $sequenceRegistry;

    /**
     * @var \Magento\Framework\EntityManager\HydratorPool
     */
    private $hydratorPool;

    /**
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\EntityManager\TypeResolver $typeResolver
     * @param \Magento\Framework\EntityManager\Sequence\SequenceManager $sequenceManager
     * @param \Magento\Framework\EntityManager\Sequence\SequenceRegistry $sequenceRegistry
     * @param \Magento\Framework\EntityManager\HydratorPool $hydratorPool
     */
    public function __construct(
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\EntityManager\TypeResolver $typeResolver,
        \Magento\Framework\EntityManager\Sequence\SequenceManager $sequenceManager,
        \Magento\Framework\EntityManager\Sequence\SequenceRegistry $sequenceRegistry,
        \Magento\Framework\EntityManager\HydratorPool $hydratorPool
    ) {
        $this->metadataPool = $metadataPool;
        $this->typeResolver = $typeResolver;
        $this->sequenceManager = $sequenceManager;
        $this->sequenceRegistry = $sequenceRegistry;
        $this->hydratorPool = $hydratorPool;
    }

    /**
     * Applies sequence identifier to given entity.
     *
     * In case sequence for given entity is not configured in corresponding di.xml file,
     * the entity will be returned without any changes.
     *
     * @param object $entity
     *
     * @return object
     */
    public function apply($entity)
    {
        $entityType = $this->typeResolver->resolve($entity);

        /** @var \Magento\Framework\DB\Sequence\SequenceInterface|null $sequence */
        $sequence = $this->sequenceRegistry->retrieve($entityType)['sequence'];

        if ($sequence) {
            $metadata = $this->metadataPool->getMetadata($entityType);
            $hydrator = $this->hydratorPool->getHydrator($entityType);

            $entityData = $hydrator->extract($entity);

            // Object already has identifier.
            if (isset($entityData[$metadata->getIdentifierField()]) && $entityData[$metadata->getIdentifierField()]) {
                $this->sequenceManager->force($entityType, $entityData[$metadata->getIdentifierField()]);
            } else {
                $entityData[$metadata->getIdentifierField()] = $sequence->getNextValue();

                $entity = $hydrator->hydrate($entity, $entityData);
            }
        }

        return $entity;
    }
}
