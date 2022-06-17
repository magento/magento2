<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\ResourceModel;

use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Entity attribute values resource
 */
class AttributeValue
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
     * @var Config
     */
    private $config;

    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param Config $config
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        Config $config
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->config = $config;
    }

    /**
     * Get attribute values for given entity type, entity ID, attribute codes and store IDs
     *
     * @param string $entityType
     * @param int $entityId
     * @param string[] $attributeCodes
     * @param int[] $storeIds
     * @return array
     */
    public function getValues(
        string $entityType,
        int $entityId,
        array $attributeCodes = [],
        array $storeIds = []
    ): array {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $metadata->getEntityConnection();
        $selects = [];
        $attributeTables = [];
        $attributes = [];
        $allAttributes = $this->getEntityAttributes($entityType);
        $result = [];
        if ($attributeCodes) {
            foreach ($attributeCodes as $attributeCode) {
                $attributes[$attributeCode] = $allAttributes[$attributeCode];
            }
        } else {
            $attributes = $allAttributes;
        }

        foreach ($attributes as $attribute) {
            if (!$attribute->isStatic()) {
                $attributeTables[$attribute->getBackend()->getTable()][] = $attribute->getAttributeId();
            }
        }

        if ($attributeTables) {
            foreach ($attributeTables as $attributeTable => $attributeIds) {
                $select = $connection->select()
                    ->from(
                        ['t' => $attributeTable],
                        ['*']
                    )
                    ->where($metadata->getLinkField() . ' = ?', $entityId)
                    ->where('attribute_id IN (?)', $attributeIds);
                if (!empty($storeIds)) {
                    $select->where(
                        'store_id IN (?)',
                        $storeIds
                    );
                }
                $selects[] = $select;
            }

            if (count($selects) > 1) {
                $select = $connection->select();
                $select->from(['u' => new UnionExpression($selects, Select::SQL_UNION_ALL, '( %s )')]);
            } else {
                $select = reset($selects);
            }

            $result = $connection->fetchAll($select);
        }

        return $result;
    }

    /**
     * Delete attribute values
     *
     * @param string $entityType
     * @param array[] $values
     * Format:
     * array(
     *      0 => array(
     *          value_id => 1,
     *          attribute_id => 11
     *      ),
     *      1 => array(
     *          value_id => 2,
     *          attribute_id => 22
     *      )
     * )
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteValues(string $entityType, array $values): void
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $metadata->getEntityConnection();
        $attributeTables = [];
        $allAttributes = [];

        foreach ($this->getEntityAttributes($entityType) as $attribute) {
            $allAttributes[(int) $attribute->getAttributeId()] = $attribute;
        }

        foreach ($values as $value) {
            $attribute = $allAttributes[(int) $value['attribute_id']] ?? null;
            if ($attribute && !$attribute->isStatic()) {
                $attributeTables[$attribute->getBackend()->getTable()][] = (int) $value['value_id'];
            }
        }

        foreach ($attributeTables as $attributeTable => $valueIds) {
            $connection->delete(
                $attributeTable,
                [
                    'value_id IN (?)' => $valueIds
                ]
            );
        }
    }

    /**
     * Insert attribute values
     *
     * @param string $entityType
     * @param array[] $values
     * Format:
     * array(
     *      0 => array(
     *          attribute_id => 11,
     *          value => 'some long text',
     *          ...
     *      ),
     *      1 => array(
     *          attribute_id => 22,
     *          value => 'some short text',
     *          ...
     *      )
     * )
     */
    public function insertValues(string $entityType, array $values): void
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $metadata->getEntityConnection();
        $attributeTables = [];
        $allAttributes = [];

        foreach ($this->getEntityAttributes($entityType) as $attribute) {
            $allAttributes[(int) $attribute->getAttributeId()] = $attribute;
        }

        foreach ($values as $value) {
            $attribute = $allAttributes[(int) $value['attribute_id']] ?? null;
            if ($attribute && !$attribute->isStatic()) {
                $columns = array_keys($value);
                $columnsHash = implode(',', $columns);
                $attributeTable = $attribute->getBackend()->getTable();
                $attributeTables[$attributeTable][$columnsHash][] = array_values($value);
            }
        }

        foreach ($attributeTables as $table => $tableData) {
            foreach ($tableData as $columns => $data) {
                $connection->insertArray(
                    $table,
                    explode(',', $columns),
                    $data
                );
            }
        }
    }

    /**
     * Get attribute of given entity type
     *
     * @param string $entityType
     * @return array
     */
    private function getEntityAttributes(string $entityType): array
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $eavEntityType = $metadata->getEavEntityType();
        return null === $eavEntityType ? [] : $this->config->getEntityAttributes($eavEntityType);
    }
}
