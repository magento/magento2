<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Resource\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;

/**
 * Catalog product media gallery attribute backend resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Media extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    const GALLERY_TABLE = 'catalog_product_entity_media_gallery';

    const GALLERY_VALUE_TABLE = 'catalog_product_entity_media_gallery_value';

    const GALLERY_VALUE_TO_ENTITY_TABLE = 'catalog_product_entity_media_gallery_value_to_entity';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::GALLERY_TABLE, 'value_id');
    }

    /**
     * @param Product $product
     * @param int $attributeId
     * @return array
     */
    public function loadProductGalleryByAttributeId($product, $attributeId)
    {
        $select = $this->createBaseLoadSelect($product->getId(), $product->getStoreId(), $attributeId);
        $result = $this->getConnection()->fetchAll($select);
        $this->_removeDuplicates($result);
        return $result;
    }

    /**
     * @param string $tableNameAlias
     * @param array $ids
     * @param int|null $storeId
     * @param array|null $cols
     * @param array $leftJoinTables
     * @param string $whereCondition
     * @return array
     */
    public function loadDataFromTableByValueId(
        $tableNameAlias,
        array $ids,
        $storeId = null,
        array $cols = null,
        array $leftJoinTables = [],
        $whereCondition = null
    ) {
        if (null == $cols) {
            $cols = '*';
        }
        $connection = $this->getConnection();
        $mainTableAlias = $this->getMainTableAlias();
        $select = $connection->select()
            ->from(
                [$mainTableAlias => $this->getTable($tableNameAlias)], $cols
            )->where(
                $mainTableAlias.'.value_id IN(?)',
                $ids
            );
        if (null !== $storeId) {
            $select->where($mainTableAlias.'.store_id = ?', $storeId);
        }
        if (null !== $whereCondition) {
            $select->where($whereCondition);
        }
        foreach ($leftJoinTables as $joinParameters) {
            $select->joinLeft($joinParameters[0], $joinParameters[1], $joinParameters[2]);
        }
        $result = $this->getConnection()->fetchAll($select);

        return $result;
    }

    /**
     * @param string $tableName
     * @param array $data
     * @param array $idKeyNames
     * @return mixed
     */
    public function updateTable($tableName, array $data, array $idKeyNames = ['value_id'])
    {
        $tableName = $this->getTable($tableName);
        $data = $this->_prepareDataForTable(
            new \Magento\Framework\DataObject($data),
            $tableName
        );
        $selectCondition = [];
        foreach ($idKeyNames as $key) {
            if (isset($data[$key])) {
                $selectCondition[] = $this->getConnection()->quoteInto($key . ' = ?', $data[$key]);
            }
        }
        $selectCondition = implode(' AND ', $selectCondition);
        $id = null;
        if (!$this->isRecordsExist($tableName, $selectCondition)) {
            $this->getConnection()->insert($tableName, $data);
            $id = $this->getConnection()->lastInsertId($tableName);
        } else {
            $this->getConnection()->update($tableName, $data, $selectCondition);
        }

        return $id;
    }

    /**
     * @param int $valueId
     * @param int $entityId
     */
    public function bindValueToEntity($valueId, $entityId)
    {
        $table = $this->getTable(self::GALLERY_VALUE_TO_ENTITY_TABLE);
        $conditions = implode(
            ' AND ',
            [
                $this->getConnection()->quoteInto('value_id = ?', (int)$valueId),
                $this->getConnection()->quoteInto('entity_id = ?', (int)$entityId)
            ]
        );
        if (!$this->isRecordsExist($table, $conditions)) {
            $this->getConnection()->insert($table, ['value_id' => $valueId, 'entity_id' => $entityId]);
        }
    }

    /**
     * @param $tableName
     * @param $condition
     * @return bool
     */
    protected function isRecordsExist($tableName, $condition)
    {
        $select = $this->getConnection()->select()->from($tableName)->where($condition);
        $result = $this->getConnection()->fetchAll($select);
        return (bool)$result;
    }

    /**
     * @return string
     */
    public function getMainTableAlias()
    {
        return 'main';
    }

    /**
     * @param int $entityId
     * @param int $storeId
     * @param int $attributeId
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createBaseLoadSelect($entityId, $storeId, $attributeId)
    {
        $connection = $this->getConnection();

        $positionCheckSql = $connection->getCheckSql(
            'value.position IS NULL',
            'default_value.position',
            'value.position'
        );

        $mainTableAlias = $this->getMainTableAlias();
        $select = $connection->select()->from(
            [$mainTableAlias => $this->getMainTable()],
            [
                'value_id',
                'file' => 'value',
                'media_type' => 'media_type'
            ]
        )->joinInner(
            ['entity' => $this->getTable(self::GALLERY_VALUE_TO_ENTITY_TABLE)],
            $mainTableAlias.'.value_id = entity.value_id',
            ['entity_id' => 'entity_id']
        )->joinLeft(
            ['value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
            implode(
                ' AND ',
                [
                    $mainTableAlias.'.value_id = value.value_id',
                    $connection->quoteInto('value.store_id = ?', (int)$storeId),
                    $connection->quoteInto('value.entity_id = ?', (int)$entityId)
                ]
            ),
            ['label', 'position', 'disabled']
        )->joinLeft(
            ['default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
            implode(
                ' AND ',
                [
                    $mainTableAlias.'.value_id = default_value.value_id',
                    'default_value.store_id = 0',
                    $connection->quoteInto('default_value.entity_id = ?', (int)$entityId)
                ]
            ),
            ['label_default' => 'label', 'position_default' => 'position', 'disabled_default' => 'disabled']
        )->where(
            $mainTableAlias.'.attribute_id = ?',
            $attributeId
        )->where(
            $mainTableAlias.'.disabled = 0'
        )->where(
            'entity.entity_id = ?',
            $entityId
        )
//            ->where($positionCheckSql . ' IS NOT NULL')
            ->order($positionCheckSql . ' ' . \Magento\Framework\DB\Select::SQL_ASC);

        return $select;
    }

    /**
     * Remove duplicates
     *
     * @param array &$result
     * @return $this
     */
    protected function _removeDuplicates(&$result)
    {
        $fileToId = [];

        foreach (array_keys($result) as $index) {
            if (!isset($fileToId[$result[$index]['file']])) {
                $fileToId[$result[$index]['file']] = $result[$index]['value_id'];
            } elseif ($fileToId[$result[$index]['file']] != $result[$index]['value_id']) {
                $this->deleteGallery($result[$index]['value_id']);
                unset($result[$index]);
            }
        }

        $result = array_values($result);
        return $this;
    }

    /**
     * Insert gallery value to db and retrieve last id
     *
     * @param array $data
     * @return integer
     */
    public function insertGallery($data)
    {
        $connection = $this->getConnection();
        $data = $this->_prepareDataForTable(new \Magento\Framework\DataObject($data), $this->getMainTable());
        $connection->insert($this->getMainTable(), $data);

        return $connection->lastInsertId($this->getMainTable());
    }

    /**
     * Delete gallery value in db
     *
     * @param array|integer $valueId
     * @return $this
     */
    public function deleteGallery($valueId)
    {
        if (is_array($valueId) && count($valueId) > 0) {
            $condition = $this->getConnection()->quoteInto('value_id IN(?) ', $valueId);
        } elseif (!is_array($valueId)) {
            $condition = $this->getConnection()->quoteInto('value_id = ? ', $valueId);
        } else {
            return $this;
        }

        $this->getConnection()->delete($this->getMainTable(), $condition);
        return $this;
    }

    /**
     * Insert gallery value for store to db
     *
     * @param array $data
     * @return $this
     */
    public function insertGalleryValueInStore($data)
    {
        $data = $this->_prepareDataForTable(
            new \Magento\Framework\DataObject($data),
            $this->getTable(self::GALLERY_VALUE_TABLE)
        );
        $this->getConnection()->insert($this->getTable(self::GALLERY_VALUE_TABLE), $data);

        return $this;
    }

    /**
     * Delete gallery value for store in db
     *
     * @param int $valueId
     * @param int $entityId
     * @param int $storeId
     * @return $this
     */
    public function deleteGalleryValueInStore($valueId, $entityId, $storeId)
    {
        $connection = $this->getConnection();

        $conditions = implode(
            ' AND ',
            [
                $connection->quoteInto('value_id = ?', (int)$valueId),
                $connection->quoteInto('entity_id = ?', (int)$entityId),
                $connection->quoteInto('store_id = ?', (int)$storeId)
            ]
        );

        $connection->delete($this->getTable(self::GALLERY_VALUE_TABLE), $conditions);

        return $this;
    }

    /**
     * Duplicates gallery db values
     *
     * @param int $attributeId
     * @param array $newFiles
     * @param int $originalProductId
     * @param int $newProductId
     * @return $this
     */
    public function duplicate($attributeId, $newFiles, $originalProductId, $newProductId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['value_id', 'value']
        )->where(
            'attribute_id = ?',
            $attributeId
        )->where(
            'entity_id = ?',
            $originalProductId
        );

        $valueIdMap = [];
        // Duplicate main entries of gallery
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $data = [
                'attribute_id' => $attributeId,
                'entity_id' => $newProductId,
                'value' => isset($newFiles[$row['value_id']]) ? $newFiles[$row['value_id']] : $row['value'],
            ];

            $valueIdMap[$row['value_id']] = $this->insertGallery($data);
        }

        if (count($valueIdMap) == 0) {
            return $this;
        }

        // Duplicate per store gallery values
        $select = $this->getConnection()->select()->from(
            $this->getTable(self::GALLERY_VALUE_TABLE)
        )->where(
            'value_id IN(?)',
            array_keys($valueIdMap)
        );

        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $row['value_id'] = $valueIdMap[$row['value_id']];
            $this->insertGalleryValueInStore($row);
        }

        return $this;
    }
}
