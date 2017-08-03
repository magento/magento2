<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\ResourceModel\Operation;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Phrase;

/**
 * Create operation for list of bulk operations.
 * @since 2.2.0
 */
class Create implements \Magento\Framework\EntityManager\Operation\CreateInterface
{
    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @var TypeResolver
     * @since 2.2.0
     */
    private $typeResolver;

    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @param MetadataPool $metadataPool
     * @param TypeResolver $typeResolver
     * @param ResourceConnection $resourceConnection
     * @since 2.2.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        TypeResolver $typeResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->metadataPool = $metadataPool;
        $this->typeResolver = $typeResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Save all operations from the list in one query.
     *
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function execute($entity, $arguments = [])
    {
        $entityType = $this->typeResolver->resolve($entity);
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $this->resourceConnection->getConnection($metadata->getEntityConnectionName());
        try {
            $connection->beginTransaction();
            $data = [];
            foreach ($entity->getItems() as $operation) {
                $data[] = $operation->getData();
            }
            $connection->insertOnDuplicate(
                $metadata->getEntityTable(),
                $data,
                [
                    'status',
                    'error_code',
                    'result_message',
                ]
            );
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $entity;
    }
}
