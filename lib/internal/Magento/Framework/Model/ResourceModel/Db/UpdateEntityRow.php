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
 * @since 2.1.0
 */
class UpdateEntityRow
{
    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     * @since 2.1.0
     */
    public function __construct(
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param EntityMetadata $metadata
     * @param array $data
     * @return array
     * @since 2.1.0
     */
    protected function prepareData(EntityMetadata $metadata, $data)
    {
        $output = [];
        foreach ($metadata->getEntityConnection()->describeTable($metadata->getEntityTable()) as $column) {
            if ($column['DEFAULT'] == 'CURRENT_TIMESTAMP' || $column['IDENTITY']) {
                continue;
            }
            if (array_key_exists(strtolower($column['COLUMN_NAME']), $data)) {
                $output[strtolower($column['COLUMN_NAME'])] = $data[strtolower($column['COLUMN_NAME'])];
            }
        }
        return $output;
    }

    /**
     * @param string $entityType
     * @param array $data
     * @return bool
     * @throws \Exception
     * @since 2.1.0
     */
    public function execute($entityType, $data)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $metadata->getEntityConnection();
        return $connection->update(
            $metadata->getEntityTable(),
            $this->prepareData($metadata, $data),
            [$metadata->getLinkField() . ' = ?' => $data[$metadata->getLinkField()]]
        );
    }
}
