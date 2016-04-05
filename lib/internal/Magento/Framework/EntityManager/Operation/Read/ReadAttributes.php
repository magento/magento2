<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Read;

use Magento\Framework\EntityManager\Operation\AttributePool;
use Magento\Framework\EntityManager\HydratorPool;

/**
 * Class ReadAttributes
 */
class ReadAttributes
{
    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @var AttributePool
     */
    private $attributePool;

    /**
     * ReadAttributes constructor.
     *
     * @param HydratorPool $hydratorPool
     * @param AttributePool $attributePool
     */
    public function __construct(
        HydratorPool $hydratorPool,
        AttributePool $attributePool
    ) {
        $this->hydratorPool = $hydratorPool;
        $this->attributePool = $attributePool;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param array $arguments
     * @return object
     */
    public function execute($entityType, $entity, $arguments = [])
    {
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $entityData = $hydrator->extract($entity);
        $actions = $this->attributePool->getActions($entityType, 'read');
        foreach ($actions as $action) {
            $entityData = array_merge($entityData, $action->execute($entityType, $entityData, $arguments));
        }
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
