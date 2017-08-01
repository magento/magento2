<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation\Create;

use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Operation\AttributePool;

/**
 * Class CreateAttributes
 * @since 2.1.0
 */
class CreateAttributes
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
     * @var AttributePool
     * @since 2.1.0
     */
    private $attributePool;

    /**
     * @param TypeResolver $typeResolver
     * @param HydratorPool $hydratorPool
     * @param AttributePool $attributePool
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function execute($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $entityData = array_merge($hydrator->extract($entity), $arguments);
        $actions = $this->attributePool->getActions($entityType, 'create');
        foreach ($actions as $action) {
            $action->execute($entityType, $entityData, $arguments);
        }
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
