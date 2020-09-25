<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;

/**
 * Catalog product media gallery resource model.
 *
 * @api
 * @since 101.0.0
 */
class Gallery extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const GALLERY_TABLE = 'catalog_product_entity_media_gallery';

    const GALLERY_VALUE_TABLE = 'catalog_product_entity_media_gallery_value';

    const GALLERY_VALUE_TO_ENTITY_TABLE = 'catalog_product_entity_media_gallery_value_to_entity';
    /**#@-*/

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata
     * @since 101.0.0
     */
    protected $metadata;
    /**
     * @var Config|null
     */
    private $mediaConfig;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param string $connectionName
     * @param Config|null $mediaConfig
     * @throws \Exception
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $connectionName = null,
        ?Config $mediaConfig = null
    ) {
        $this->metadata = $metadataPool->getMetadata(
            \Magento\Catalog\Api\Data\ProductInterface::class
        );

        parent::__construct($context, $connectionName);
        $this->mediaConfig = $mediaConfig ?? ObjectManager::getInstance()->get(Config::class);
    }

    /**
     * @inheritdoc
     *
     * @since 101.0.0
     */
    protected function _construct()
    {
        $this->_init(self::GALLERY_TABLE, 'value_id');
    }

    /**
     * @inheritdoc
     *
     * @since 101.0.0
     */
    public function getConnection()
    {
        return $this->metadata->getEntityConnection();
    }

    /**
     * Load data from table by valueId
     *
     * @param string $tableNameAlias
     * @param array $ids
     * @param int|null $storeId
     * @param array|null $cols
     * @param array $leftJoinTables
     * @param string $whereCondition
     * @return array
     * @since 101.0.0
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
        $mainTableAlias = $this->getMainTableAlias();
        $select = $this->getConnection()->select()
            ->from(
                [$mainTableAlias => $this->getTable($tableNameAlias)],
                $cols
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
     * Load product gallery by attributeId
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $attributeId
     * @return array
     * @since 101.0.0
     */
    public function loadProductGalleryByAttributeId($product, $attributeId)
    {
        $select = $this->createBaseLoadSelect(
            $product->getData($this->metadata->getLinkField()),
            $product->getStoreId(),
            $attributeId
        );

        $result = $this->getConnection()->fetchAll($select);

        $this->removeDuplicates($result);

        return $result;
    }

    /**
     * Create base load select
     *
     * @param int $entityId
     * @param int $storeId
     * @param int $attributeId
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 101.0.0
     */
    protected function createBaseLoadSelect($entityId, $storeId, $attributeId)
    {
        $select = $this->createBatchBaseSelect($storeId, $attributeId);

        $select = $select->where(
            'entity.' . $this->metadata->getLinkField() . ' = ?',
            $entityId
        );
        return $select;
    }

    /**
     * Create batch base select
     *
     * @param int $storeId
     * @param int $attributeId
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 101.0.1
     */
    public function createBatchBaseSelect($storeId, $attributeId)
    {
        $linkField = $this->metadata->getLinkField();

        $positionCheckSql = $this->getConnection()->getCheckSql(
            'value.position IS NULL',
            'default_value.position',
            'value.position'
        );

        $mainTableAlias = $this->getMainTableAlias();

        $select = $this->getConnection()->select()->from(
            [$mainTableAlias => $this->getMainTable()],
            [
                'value_id',
                'file' => 'value',
                'media_type'
            ]
        )->joinInner(
            ['entity' => $this->getTable(self::GALLERY_VALUE_TO_ENTITY_TABLE)],
            $mainTableAlias . '.value_id = entity.value_id',
            [$linkField]
        )->joinLeft(
            ['value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
            implode(
                ' AND ',
                [
                    $mainTableAlias . '.value_id = value.value_id',
                    $this->getConnection()->quoteInto('value.store_id = ?', (int)$storeId),
                    'value.' . $linkField . ' = entity.' . $linkField,
                ]
            ),
            []
        )->joinLeft(
            ['default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
            implode(
                ' AND ',
                [
                    $mainTableAlias . '.value_id = default_value.value_id',
                    $this->getConnection()->quoteInto('default_value.store_id = ?', Store::DEFAULT_STORE_ID),
                    'default_value.' . $linkField . ' = entity.' . $linkField,
                ]
            ),
            []
        )->columns([
            'label' => $this->getConnection()->getIfNullSql('`value`.`label`', '`default_value`.`label`'),
            'position' => $this->getConnection()->getIfNullSql('`value`.`position`', '`default_value`.`position`'),
            'disabled' => $this->getConnection()->getIfNullSql('`value`.`disabled`', '`default_value`.`disabled`'),
            'label_default' => 'default_value.label',
            'position_default' => 'default_value.position',
            'disabled_default' => 'default_value.disabled'
        ])->where(
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
     * Removes duplicates.
     *
     * @param array &$result
     * @return $this
     * @since 101.0.0
     */
    protected function removeDuplicates(&$result)
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
     * Get main table alias
     *
     * @return string
     * @since 101.0.0
     */
    public function getMainTableAlias()
    {
        return 'main';
    }

    /**
     * Bind value to entity
     *
     * @param int $valueId
     * @param int $entityId
     * @return int
     * @since 101.0.0
     */
    public function bindValueToEntity($valueId, $entityId)
    {
        return $this->saveDataRow(
            self::GALLERY_VALUE_TO_ENTITY_TABLE,
            [
                'value_id' => $valueId,
                $this->metadata->getLinkField() => $entityId
            ]
        );
    }

    /**
     * Save data row
     *
     * @param string $table
     * @param array $data
     * @param array $fields
     * @return int
     * @since 101.0.0
     */
    public function saveDataRow($table, array $data, array $fields = [])
    {
        $table = $this->getTable($table);
        return $this->getConnection()->insertOnDuplicate($table, $data, $fields);
    }

    /**
     * Inserts gallery value to DB and retrieve last Id.
     *
     * @param array $data
     * @return int
     * @since 101.0.0
     */
    public function insertGallery($data)
    {
        $data = $this->_prepareDataForTable(
            new \Magento\Framework\DataObject($data),
            $this->getMainTable()
        );

        $this->getConnection()->insert($this->getMainTable(), $data);

        return $this->getConnection()->lastInsertId($this->getMainTable());
    }

    /**
     * Deletes gallery value in Db.
     *
     * @param array|integer $valueId
     * @return $this
     * @since 101.0.0
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
     * Inserts gallery value for store to Db.
     *
     * @param array $data
     * @return $this
     * @since 101.0.0
     */
    public function insertGalleryValueInStore($data)
    {
        $data = $this->_prepareDataForTable(
            new \Magento\Framework\DataObject($data),
            $this->getTable(self::GALLERY_VALUE_TABLE)
        );

        $this->getConnection()->insert(
            $this->getTable(self::GALLERY_VALUE_TABLE),
            $data
        );

        return $this;
    }

    /**
     * Deletes gallery value for store in DB.
     *
     * @param int $valueId
     * @param int $entityId
     * @param int $storeId
     * @return $this
     * @since 101.0.0
     */
    public function deleteGalleryValueInStore($valueId, $entityId, $storeId)
    {
        $conditions = implode(
            ' AND ',
            [
                $this->getConnection()->quoteInto('value_id = ?', (int)$valueId),
                $this->getConnection()->quoteInto($this->metadata->getLinkField() . ' = ?', (int)$entityId),
                $this->getConnection()->quoteInto('store_id = ?', (int)$storeId)
            ]
        );

        $this->getConnection()->delete(
            $this->getTable(self::GALLERY_VALUE_TABLE),
            $conditions
        );

        return $this;
    }

    /**
     * Duplicates gallery DB values.
     *
     * @param int $attributeId
     * @param array $newFiles
     * @param int $originalProductId
     * @param int $newProductId
     * @return array
     * @since 101.0.0
     */
    public function duplicate($attributeId, $newFiles, $originalProductId, $newProductId)
    {
        $linkField = $this->metadata->getLinkField();

        $select = $this->getConnection()->select()->from(
            [$this->getMainTableAlias() => $this->getMainTable()],
            ['value_id', 'value', 'media_type', 'disabled']
        )->joinInner(
            ['entity' => $this->getTable(self::GALLERY_VALUE_TO_ENTITY_TABLE)],
            $this->getMainTableAlias() . '.value_id = entity.value_id',
            [$linkField]
        )->where(
            'attribute_id = ?',
            $attributeId
        )->where(
            'entity.' . $linkField . ' = ?',
            $originalProductId
        );

        $valueIdMap = [];

        // Duplicate main entries of gallery
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $data = $row;
            $data['attribute_id'] = $attributeId;
            $data['value'] = $newFiles[$row['value_id']] ?? $row['value'];
            unset($data['value_id']);

            $valueIdMap[$row['value_id']] = $this->insertGallery($data);
            $this->bindValueToEntity($valueIdMap[$row['value_id']], $newProductId);
        }

        if (count($valueIdMap) === 0) {
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
            unset($row['record_id']);

            $row[$linkField] = $newProductId;
            $row['value_id'] = $valueIdMap[$row['value_id']];

            $this->insertGalleryValueInStore($row);
        }

        return $valueIdMap;
    }

    /**
     * Returns product images in specific stores.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int|array $storeIds
     * @return array
     * @since 101.0.0
     */
    public function getProductImages($product, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }

        $mainTable = $product->getResource()->getAttribute('image')->getBackend()->getTable();

        $select = $this->getConnection()->select()->from(
            ['images' => $mainTable],
            ['value as filepath', 'store_id']
        )->joinLeft(
            ['attr' => $this->getTable('eav_attribute')],
            'images.attribute_id = attr.attribute_id',
            ['attribute_code']
        )->where(
            $this->metadata->getLinkField() . ' = ?',
            $product->getData($this->metadata->getLinkField())
        )->where(
            'store_id IN (?)',
            $storeIds,
            \Zend_Db::INT_TYPE
        )->where(
            'attribute_code IN (?)',
            $this->mediaConfig->getMediaAttributeCodes()
        );

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Counts uses of this image.
     *
     * @param string $image
     * @return int
     * @since 101.0.8
     */
    public function countImageUses($image)
    {
        $select = $this->getConnection()->select()
            ->from([$this->getMainTableAlias() => $this->getMainTable()])
            ->where(
                'value = ?',
                $image
            );
        return count($this->getConnection()->fetchAll($select));
    }
}
