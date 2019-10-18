<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class ReadEntityRow
 */
class CreateEntityRow
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

    /**
     * Prepare data.
     *
     * @param EntityMetadata $metadata
     * @param array $data
     * @return array
     */
    protected function prepareData(EntityMetadata $metadata, $data)
    {
        $output = [];
        foreach ($metadata->getEntityConnection()->describeTable($metadata->getEntityTable()) as $column) {
            if ($column['DEFAULT'] == 'CURRENT_TIMESTAMP') {
                continue;
            }
            if (isset($data[strtolower($column['COLUMN_NAME'])])) {
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
     * Create entity row.
     *
     * @param string $entityType
     * @param array $data
     * @return array
     */
    public function execute($entityType, $data)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);

        $linkField = $metadata->getLinkField();
        $entityTable = $metadata->getEntityTable();
        $connection = $metadata->getEntityConnection();

        $connection->insert($entityTable, $this->prepareData($metadata, $data));

        $data[$linkField] = $connection->lastInsertId($entityTable);

        return $data;
    }
}
