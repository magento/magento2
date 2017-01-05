<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\Entity;

use Magento\Framework\EntityManager\MapperInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;

/**
 * @deprecated
 */
class BulkSummaryMapper implements MapperInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
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
     */
    public function databaseToEntity($entityType, $data)
    {
        return $data;
    }
}
