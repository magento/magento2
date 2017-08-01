<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation\Create;

use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Db\CreateRow;

/**
 * Class CreateMain
 * @since 2.1.0
 */
class CreateMain
{
    /**
     * @var TypeResolver
     * @since 2.1.0
     */
    private $typeResolver;

    /**
     * @var HydratorPool
     * @since 2.1.0
     */
    private $hydratorPool;

    /**
     * @var CreateRow
     * @since 2.1.0
     */
    private $createRow;

    /**
     * @param TypeResolver $typeResolver
     * @param HydratorPool $hydratorPool
     * @param CreateRow $createRow
     * @since 2.1.0
     */
    public function __construct(
        TypeResolver $typeResolver,
        HydratorPool $hydratorPool,
        CreateRow $createRow
    ) {
        $this->typeResolver = $typeResolver;
        $this->hydratorPool = $hydratorPool;
        $this->createRow = $createRow;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @since 2.1.0
     */
    public function execute($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $arguments = array_merge($hydrator->extract($entity), $arguments);
        $entityData = $this->createRow->execute($entityType, $arguments);
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
