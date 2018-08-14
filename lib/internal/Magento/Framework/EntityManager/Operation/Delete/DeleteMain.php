<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation\Delete;

use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Db\DeleteRow;

/**
 * Class DeleteMain
 */
class DeleteMain
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
     * @var DeleteRow
     */
    private $deleteRow;

    /**
     * @param TypeResolver $typeResolver
     * @param HydratorPool $hydratorPool
     * @param DeleteRow $deleteRow
     */
    public function __construct(
        TypeResolver $typeResolver,
        HydratorPool $hydratorPool,
        DeleteRow $deleteRow
    ) {
        $this->typeResolver = $typeResolver;
        $this->hydratorPool = $hydratorPool;
        $this->deleteRow = $deleteRow;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $arguments = array_merge($hydrator->extract($entity), $arguments);
        $this->deleteRow->execute($entityType, $arguments);
        return $entity;
    }
}
