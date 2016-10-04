<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Db;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Class CreateRow
 */
class CreateRow
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
     * @param EntityMetadataInterface $metadata
     * @param AdapterInterface $connection
     * @param array $data
     * @return array
     */
    protected function prepareData(EntityMetadataInterface $metadata, AdapterInterface $connection, $data)
    {
        $output = [];
        foreach ($connection->describeTable($metadata->getEntityTable()) as $column) {
            $columnName = strtolower($column['COLUMN_NAME']);
            if ($this->canNotSetTimeStamp($columnName, $column, $data)) {
                continue;
            }

            if (isset($data[$columnName])) {
                $output[strtolower($column['COLUMN_NAME'])] = $data[strtolower($column['COLUMN_NAME'])];
            } elseif ($column['DEFAULT'] === null) {
                $output[strtolower($column['COLUMN_NAME'])] = null;
            }
        }
        if (empty($data[$metadata->getIdentifierField()])) {
            $output[$metadata->getIdentifierField()] = $metadata->generateIdentifier();
        }
        return $output;
    }

    /**
     * @param string $columnName
     * @param string $column
     * @param array $data
     * @return bool
     */
    private function canNotSetTimeStamp($columnName, $column, array $data)
    {
        return $column['DEFAULT'] == 'CURRENT_TIMESTAMP' && !isset($data[$columnName])
        && empty($column['NULLABLE']);
    }

    /**
     * @param string $entityType
     * @param array $data
     * @return array
     */
    public function execute($entityType, $data)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $linkField = $metadata->getLinkField();
        $entityTable = $metadata->getEntityTable();
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        $connection->insert($entityTable, $this->prepareData($metadata, $connection, $data));
        $data[$linkField] = $connection->lastInsertId($entityTable);

        return $data;
    }
}
