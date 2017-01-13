<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Operation;

use Magento\Framework\EntityManager\Operation\CheckIfExistsInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\App\ResourceConnection;

/**
 * Class CheckIfExists
 */
class CheckIfExists implements CheckIfExistsInterface
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
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param TypeResolver $typeResolver
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        TypeResolver $typeResolver,
        MetadataPool $metadataPool,
        HydratorPool $hydratorPool,
        ResourceConnection $resourceConnection
    ) {
        $this->metadataPool = $metadataPool;
        $this->hydratorPool = $hydratorPool;
        $this->typeResolver = $typeResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
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
