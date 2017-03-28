<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Read;

use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Db\ReadRow;

class ReadMain
{
    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @var ReadRow
     */
    private $readRow;

    /**
     * @param TypeResolver $typeResolver
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param ReadRow $readRow
     */
    public function __construct(
        TypeResolver $typeResolver,
        MetadataPool $metadataPool,
        HydratorPool $hydratorPool,
        ReadRow $readRow
    ) {
        $this->typeResolver = $typeResolver;
        $this->metadataPool = $metadataPool;
        $this->hydratorPool = $hydratorPool;
        $this->readRow = $readRow;
    }

    /**
     * @param object $entity
     * @param string $identifier
     * @return object
     */
    public function execute($entity, $identifier)
    {
        $entityType = $this->typeResolver->resolve($entity);
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $entityData = $this->readRow->execute($entityType, $identifier);
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
