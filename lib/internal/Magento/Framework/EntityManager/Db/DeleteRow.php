<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Db;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ResourceConnection;

/**
 * Class DeleteRow
 * @since 2.1.0
 */
class DeleteRow
{
    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     * @since 2.1.0
     */
    private $resourceConnection;

    /**
     * CreateRow constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @since 2.1.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string $entityType
     * @param array $data
     * @return int
     * @throws \Exception
     * @since 2.1.0
     */
    public function execute($entityType, $data)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        return $connection->delete(
            $metadata->getEntityTable(),
            [$metadata->getLinkField() . ' = ?' => $data[$metadata->getLinkField()]]
        );
    }
}
