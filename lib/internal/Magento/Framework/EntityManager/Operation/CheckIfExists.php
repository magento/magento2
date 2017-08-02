<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.1.0
 */
class CheckIfExists implements CheckIfExistsInterface
{
    /**
     * @var ResourceConnection
     * @since 2.1.0
     */
    private $resourceConnection;

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
     * @var TypeResolver
     * @since 2.1.0
     */
    private $typeResolver;

    /**
     * @param MetadataPool $metadataPool
     * @param HydratorPool $hydratorPool
     * @param TypeResolver $typeResolver
     * @param ResourceConnection $resourceConnection
     * @since 2.1.0
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
     * @since 2.1.0
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
