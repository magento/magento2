<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Read;

use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Db\ReadRow;

/**
 * Class \Magento\Framework\EntityManager\Operation\Read\ReadMain
 *
 * @since 2.1.0
 */
class ReadMain
{
    /**
     * @var TypeResolver
     * @since 2.1.0
     */
    private $typeResolver;

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
     * @var ReadRow
     * @since 2.1.0
     */
    private $readRow;

    /**
     * @param TypeResolver $typeResolver
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param ReadRow $readRow
     * @since 2.1.0
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
     * @since 2.1.0
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
