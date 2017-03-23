<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\HydratorPool;

/**
 * Class ValidateDataIntegrity
 */
class ValidateDataIntegrity
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
     * @var ObjectRelationProcessor
     */
    private $objectRelationProcessor;

    /**
     * ValidateDataIntegrity constructor.
     *
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param ObjectRelationProcessor $objectRelationProcessor
     */
    public function __construct(
        MetadataPool $metadataPool,
        HydratorPool $hydratorPool,
        ObjectRelationProcessor $objectRelationProcessor
    ) {
        $this->metadataPool = $metadataPool;
        $this->hydratorPool = $hydratorPool;
        $this->objectRelationProcessor = $objectRelationProcessor;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @throws \Exception
     * @return void
     */
    public function execute($entityType, $entity)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $this->objectRelationProcessor->validateDataIntegrity(
            $metadata->getEntityTable(),
            $hydrator->extract($entity)
        );
    }
}
