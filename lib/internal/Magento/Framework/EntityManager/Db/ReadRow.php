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
 */
class ReadRow
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
     * CreateRow constructor.
     *
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
     * @param string $entityType
     * @param string $identifier
     * @param array $context
     * @return array
     * @throws \Exception
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
