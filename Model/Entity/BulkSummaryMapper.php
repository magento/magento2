<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\Entity;

use Magento\Framework\EntityManager\MapperInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;

/**
 * @deprecated 2.2.0
 * @since 2.2.0
 */
class BulkSummaryMapper implements MapperInterface
{
    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @since 2.2.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function entityToDatabase($entityType, $data)
    {
        // workaround for delete/update operations that are currently using only primary key as identifier
        if (!empty($data['uuid'])) {
            $metadata = $this->metadataPool->getMetadata($entityType);
            $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
            $select = $connection->select()->from($metadata->getEntityTable(), 'id')->where("uuid = ?", $data['uuid']);
            $identifier = $connection->fetchOne($select);
            if ($identifier !== false) {
                $data['id'] = $identifier;
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.2.0
     */
    public function databaseToEntity($entityType, $data)
    {
        return $data;
    }
}
