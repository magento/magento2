<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;
use Magento\Store\Model\Store;

/**
 * Catalog product media gallery attribute backend resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Media extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
                $mainTableAlias . '.value_id IN(?)',
                $ids
            );
        if (null !== $storeId) {
            $select->where($mainTableAlias . '.store_id = ?', $storeId);
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
     * @param int $valueId
     * @param int $entityId
     * @return int
     */
    public function bindValueToEntity($valueId, $entityId)
    {
        return $this->saveDataRow(
            self::GALLERY_VALUE_TO_ENTITY_TABLE,
            [
                'value_id' => $valueId,
                'entity_id' => $entityId
            ]
        );
    }

    /**
     * @param string $table
     * @param array $data
     * @param array $fields
     * @return int
     */
    public function saveDataRow($table, array $data, array $fields = [])
    {
        $table = $this->getTable($table);
        return $this->getConnection()->insertOnDuplicate($table, $data, $fields);
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
        $select =  $this->createBatchBaseSelect($storeId, $attributeId);

        $select = $select->where(
            'entity.entity_id = ?',
            $entityId
        );
        return $select;
    }

    /**
     * @param int $storeId
     * @param int $attributeId
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createBatchBaseSelect($storeId, $attributeId)
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
            $mainTableAlias . '.value_id = entity.value_id',
            ['entity_id' => 'entity_id']
        )->joinLeft(
            ['value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
            implode(
                ' AND ',
                [
                    $mainTableAlias . '.value_id = value.value_id',
                    $connection->quoteInto('value.store_id = ?', (int)$storeId),
                ]
            ),
            ['label', 'position', 'disabled']
        )->joinLeft(
            ['default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
            implode(
                ' AND ',
                [
                    $mainTableAlias . '.value_id = default_value.value_id',
                    $connection->quoteInto('default_value.store_id = ?', Store::DEFAULT_STORE_ID),
                ]
            ),
            ['label_default' => 'label', 'position_default' => 'position', 'disabled_default' => 'disabled']
        )->where(
            $mainTableAlias . '.attribute_id = ?',
            $attributeId
        )->where(
            $mainTableAlias . '.disabled = 0'
        )->order(
            $positionCheckSql . ' ' . \Magento\Framework\DB\Select::SQL_ASC
        );

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
     * @return array
     */
    public function duplicate($attributeId, $newFiles, $originalProductId, $newProductId)
    {
        $mediaGalleryEntities = $this->loadMediaGalleryEntities($attributeId, $originalProductId);

        // Duplicate main entries of gallery
        $valueIdMap = [];
        foreach ($mediaGalleryEntities as $row) {
            $valueId = $row['value_id'];
            $data = [
                'attribute_id' => $attributeId,
                'media_type' => $row['media_type'],
                'disabled' => $row['disabled'],
                'value' => isset($newFiles[$valueId]) ? $newFiles[$valueId] : $row['value'],
            ];
            $valueIdMap[$valueId] = $this->insertGallery($data);
            $this->bindValueToEntity($valueIdMap[$valueId], $newProductId);
        }



        if (count($valueIdMap) == 0) {
            return [];
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
            unset($row['record_id']);
            $this->insertGalleryValueInStore($row);
            $this->bindValueToEntity($row['value_id'], $newProductId);
        }

        return $valueIdMap;
    }

    /**
     * @param array $valueIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadMediaGalleryEntitiesbyId($valueIds)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'value_id IN(?)',
            $valueIds
        );

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param int $attributeId
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadMediaGalleryEntities($attributeId, $productId)
    {
        $mainTableAlias = $this->getMainTableAlias();
        $select = $this->getConnection()->select()->from(
            [$mainTableAlias => $this->getMainTable()]
        )->joinInner(
            ['entity' => $this->getTable(self::GALLERY_VALUE_TO_ENTITY_TABLE)],
            $mainTableAlias . '.value_id = entity.value_id',
            ['entity_id' => 'entity_id']
        )->where(
            'attribute_id = ?',
            $attributeId
        )->where(
            'entity.entity_id = ?',
            $productId
        );

        return $this->getConnection()->fetchAll($select);
    }
}
