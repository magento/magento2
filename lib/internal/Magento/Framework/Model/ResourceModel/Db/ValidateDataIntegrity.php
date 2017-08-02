<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\HydratorPool;

/**
 * Class ValidateDataIntegrity
 * @since 2.1.0
 */
class ValidateDataIntegrity
{
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
     * @var ObjectRelationProcessor
     * @since 2.1.0
     */
    private $objectRelationProcessor;

    /**
     * ValidateDataIntegrity constructor.
     *
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param ObjectRelationProcessor $objectRelationProcessor
     * @since 2.1.0
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
     * @since 2.1.0
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
