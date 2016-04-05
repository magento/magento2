<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Read;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\App\ResourceConnection;

/**
 * Class CheckIsExists
 */
class CheckIsExists
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * CheckIsExists constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        HydratorPool $hydratorPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->hydratorPool = $hydratorPool;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param array $arguments
     * @return bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity, $arguments = [])
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        $entityData = $hydrator->extract($entity);
        if (!isset($entityData[$metadata->getIdentifierField()])) {
            return false;
        }
        return (bool)$connection->fetchOne(
            $connection->select()
                ->from($metadata->getEntityTable(), [$metadata->getIdentifierField()])
                ->where($metadata->getIdentifierField() . ' = ?', $entityData[$metadata->getIdentifierField()])
                ->limit(1)
        );
    }
}
