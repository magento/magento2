<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Db;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class DeleteRow
 * @since 2.1.0
 */
class ReadRow
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
     * @param string $identifier
     * @param array $context
     * @return array
     * @throws \Exception
     * @since 2.1.0
     */
    public function execute($entityType, $identifier, $context = [])
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        $select = $connection->select()
            ->from(['t' => $metadata->getEntityTable()])
            ->where($metadata->getIdentifierField() . ' = ?', $identifier);
        foreach ($context as $field => $value) {
            $select->where(
                $connection->quoteIdentifier($field) . ' = ?',
                $value
            );
        }
        $data = $connection->fetchRow($select);
        return $data ?: [];
    }
}
