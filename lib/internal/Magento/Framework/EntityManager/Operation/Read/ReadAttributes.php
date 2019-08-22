<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation\Read;

use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Operation\AttributePool;

/**
 * Class ReadAttributes
 */
class ReadAttributes
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
     * @var AttributePool
     */
    private $attributePool;

    /**
     * @param TypeResolver $typeResolver
     * @param HydratorPool $hydratorPool
     * @param AttributePool $attributePool
     */
    public function __construct(
        TypeResolver $typeResolver,
        HydratorPool $hydratorPool,
        AttributePool $attributePool
    ) {
        $this->typeResolver = $typeResolver;
        $this->hydratorPool = $hydratorPool;
        $this->attributePool = $attributePool;
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
        $entityData = array_merge($hydrator->extract($entity), $arguments);
        $actions = $this->attributePool->getActions($entityType, 'read');
        foreach ($actions as $action) {
            $entityData = array_merge($entityData, $action->execute($entityType, $entityData, $arguments));
        }
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
