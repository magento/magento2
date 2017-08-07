<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Db;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Class UpdateRow
 * @since 2.1.0
 */
class UpdateRow
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
     * @param EntityMetadataInterface $metadata
     * @param AdapterInterface $connection
     * @param array $data
     * @return array
     * @since 2.1.0
     */
    protected function prepareData(EntityMetadataInterface $metadata, AdapterInterface $connection, $data)
    {
        $output = [];
        foreach ($connection->describeTable($metadata->getEntityTable()) as $column) {
            $columnName = strtolower($column['COLUMN_NAME']);
            if ($this->canNotSetTimeStamp($columnName, $column, $data) || $column['IDENTITY']) {
                continue;
            }

            if (isset($data[$columnName])) {
                $output[strtolower($column['COLUMN_NAME'])] = $data[strtolower($column['COLUMN_NAME'])];
            } elseif (!empty($column['NULLABLE'])) {
                $output[strtolower($column['COLUMN_NAME'])] = null;
            }
        }
        if (empty($data[$metadata->getIdentifierField()])) {
            $output[$metadata->getIdentifierField()] = $metadata->generateIdentifier();
        }

        return $output;
    }

    /**
     * Prepares SQL conditions for an update request.
     *
     * @param EntityMetadataInterface $metadata
     * @param AdapterInterface $connection
     * @param array $data
     *
     * @return array
     * @since 2.2.0
     */
    private function prepareUpdateConditions(
        EntityMetadataInterface $metadata,
        AdapterInterface $connection,
        $data
    ) {
        $conditions = [];

        $indexList = $connection->getIndexList($metadata->getEntityTable());
        $primaryKeyName = $connection->getPrimaryKeyName($metadata->getEntityTable());

        foreach ($indexList[$primaryKeyName]['COLUMNS_LIST'] as $linkField) {
            if (isset($data[$linkField])) {
                $conditions[$linkField . ' = ?'] = $data[$linkField];
            }
        }

        return $conditions;
    }

    /**
     * @param string $columnName
     * @param string $column
     * @param array $data
     * @return bool
     * @since 2.2.0
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
     * @since 2.1.0
     */
    public function execute($entityType, $data)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);

        $connection = $this->resourceConnection->getConnectionByName(
            $metadata->getEntityConnectionName()
        );

        $connection->update(
            $metadata->getEntityTable(),
            $this->prepareData($metadata, $connection, $data),
            $this->prepareUpdateConditions($metadata, $connection, $data)
        );

        return $data;
    }
}
