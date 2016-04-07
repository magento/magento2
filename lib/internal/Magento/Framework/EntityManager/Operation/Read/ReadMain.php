<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Read;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\Db\ReadRow;

class ReadMain
{
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
     * ReadMain constructor.
     *
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param ReadRow $readRow
     */
    public function __construct(
        MetadataPool $metadataPool,
        HydratorPool $hydratorPool,
        ReadRow $readRow
    ) {
        $this->metadataPool = $metadataPool;
        $this->hydratorPool = $hydratorPool;
        $this->readRow = $readRow;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param string $identifier
     * @return object
     */
    public function execute($entityType, $entity, $identifier)
    {
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $entityData = $this->readRow->execute($entityType, $identifier);
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
