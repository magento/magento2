<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation\Update;

use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Db\UpdateRow;

/**
 * Class UpdateMain
 */
class UpdateMain
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
     * @var UpdateRow
     */
    private $updateRow;

    /**
     * @param TypeResolver $typeResolver
     * @param HydratorPool $hydratorPool
     * @param UpdateRow $updateRow
     */
    public function __construct(
        TypeResolver $typeResolver,
        HydratorPool $hydratorPool,
        UpdateRow $updateRow
    ) {
        $this->typeResolver = $typeResolver;
        $this->hydratorPool = $hydratorPool;
        $this->updateRow = $updateRow;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $arguments = array_merge($hydrator->extract($entity), $arguments);
        $entityData = $this->updateRow->execute($entityType, $arguments);
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
