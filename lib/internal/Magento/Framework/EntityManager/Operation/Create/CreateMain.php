<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation\Create;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Db\CreateRow;
use Magento\Framework\EntityManager\Sequence\SequenceApplier;

/**
 * Class CreateMain
 */
class CreateMain
{
    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @var CreateRow
     */
    private $createRow;

    /**
     * @var SequenceApplier
     */
    private $sequenceApplier;

    /**
     * @param TypeResolver $typeResolver
     * @param HydratorPool $hydratorPool
     * @param CreateRow $createRow
     * @param SequenceApplier $sequenceApplier
     */
    public function __construct(
        TypeResolver $typeResolver,
        HydratorPool $hydratorPool,
        CreateRow $createRow,
        SequenceApplier $sequenceApplier = null
    ) {
        $this->typeResolver = $typeResolver;
        $this->hydratorPool = $hydratorPool;
        $this->createRow = $createRow;
        $this->sequenceApplier = $sequenceApplier;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        $entity = $this->getSequenceApplier()->apply($entity);
        $entityType = $this->typeResolver->resolve($entity);
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $arguments = array_merge($hydrator->extract($entity), $arguments);
        $entityData = $this->createRow->execute($entityType, $arguments);
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }

    /**
     * @return SequenceApplier
     *
     * @deprecated
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
