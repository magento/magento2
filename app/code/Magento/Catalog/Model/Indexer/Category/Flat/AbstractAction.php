<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Category\Flat;

use Magento\Framework\App\ResourceConnection;

/**
 * Abstract action class for category flat indexers.
 */
class AbstractAction
{
    /**
     * Suffix for table to show it is temporary
     */
    const TEMPORARY_TABLE_SUFFIX = '_tmp';

    /**
     * Attribute codes
     *
     * @var array
     */
    protected $attributeCodes;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\ResourceModel\Helper
     */
    protected $resourceHelper;

    /**
     * Flat columns
     *
     * @var array
     */
    protected $columns = [];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata
     */
    protected $categoryMetadata;

    /**
     * Static columns to skip
     *
     * @var array
     */
    protected $skipStaticColumns = [];

    /**
     * @param ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->storeManager = $storeManager;
        $this->resourceHelper = $resourceHelper;
        $this->columns = array_merge($this->getStaticColumns(), $this->getEavColumns());
    }

    /**
     * Add suffix to table name to show it is temporary
     *
     * @param string $tableName
     * @return string
     */
    protected function addTemporaryTableSuffix($tableName)
    {
        return $tableName . self::TEMPORARY_TABLE_SUFFIX;
    }

    /**
     * Retrieve list of columns for flat structure
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Return name of table for given $storeId.
     *
     * @param integer $storeId
     * @return string
     */
    public function getMainStoreTable($storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID)
    {
        if (is_string($storeId)) {
            $storeId = (int) $storeId;
        }

        $suffix = sprintf('store_%d', $storeId);
        $table = $this->connection->getTableName($this->getTableName('catalog_category_flat_' . $suffix));

        return $table;
    }

    /**
     * Return structure for flat catalog table
     *
     * @param string $tableName
     * @return \Magento\Framework\DB\Ddl\Table
     */
    protected function getFlatTableStructure($tableName)
    {
        $table = $this->connection->newTable(
            $tableName
        )->setComment(
            'Catalog Category Flat'
        );

        //Adding columns
        foreach ($this->getColumns() as $fieldName => $fieldProp) {
            $default = $fieldProp['default'];
            if ($fieldProp['type'][0] == \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP
                && $default == 'CURRENT_TIMESTAMP'
            ) {
                $default = \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT;
            }
            $table->addColumn(
                $fieldName,
                $fieldProp['type'][0],
                $fieldProp['type'][1],
                [
                    'nullable' => $fieldProp['nullable'],
                    'unsigned' => $fieldProp['unsigned'],
                    'default' => $default,
                    'primary' => isset($fieldProp['primary']) ? $fieldProp['primary'] : false
                ],
                $fieldProp['comment'] != '' ? $fieldProp['comment'] : ucwords(str_replace('_', ' ', $fieldName))
            );
        }

        // Adding indexes
        $table->addIndex(
            $this->connection->getIndexName($tableName, ['entity_id']),
            ['entity_id'],
            ['type' => 'primary']
        );
        $table->addIndex(
            $this->connection->getIndexName($tableName, ['store_id']),
            ['store_id'],
            ['type' => 'index']
        );
        $table->addIndex(
            $this->connection->getIndexName($tableName, ['path']),
            ['path'],
            ['type' => 'index']
        );
        $table->addIndex(
            $this->connection->getIndexName($tableName, ['level']),
            ['level'],
            ['type' => 'index']
        );

        return $table;
    }

    /**
     * Return array of static columns
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getStaticColumns()
    {
        $columns = [];
        $describe = $this->connection->describeTable(
            $this->connection->getTableName($this->getTableName('catalog_category_entity'))
        );

        foreach ($describe as $column) {
            if (in_array($column['COLUMN_NAME'], $this->getSkipStaticColumns())) {
                continue;
            }
            $isUnsigned = '';
            $options = null;
            $ddlType = $this->resourceHelper->getDdlTypeByColumnType($column['DATA_TYPE']);
            $column['DEFAULT'] = trim($column['DEFAULT'], "' ");
            switch ($ddlType) {
                case \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT:
                case \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER:
                case \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT:
                    $isUnsigned = (bool)$column['UNSIGNED'];
                    if ($column['DEFAULT'] === '') {
                        $column['DEFAULT'] = null;
                    }

                    $options = null;
                    if ($column['SCALE'] > 0) {
                        $ddlType = \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL;
                    } else {
                        break;
                    }
                    // fall-through intentional
                case \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL:
                    $options = $column['PRECISION'] . ',' . $column['SCALE'];
                    $isUnsigned = null;
                    if ($column['DEFAULT'] === '') {
                        $column['DEFAULT'] = null;
                    }
                    break;
                case \Magento\Framework\DB\Ddl\Table::TYPE_TEXT:
                    $options = $column['LENGTH'];
                    $isUnsigned = null;
                    break;
                case \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP:
                    $options = null;
                    $isUnsigned = null;
                    break;
                case \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME:
                    $isUnsigned = null;
                    break;
            }
            $columns[$column['COLUMN_NAME']] = [
                'type' => [$ddlType, $options],
                'unsigned' => $isUnsigned,
                'nullable' => $column['NULLABLE'],
                'default' => $column['DEFAULT'] === null ? false : $column['DEFAULT'],
                'comment' => $column['COLUMN_NAME'],
            ];
        }
        $columns['store_id'] = [
            'type' => [\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 5],
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
            'comment' => 'Store Id',
        ];

        return $columns;
    }

    /**
     * Return array of eav columns, skip attribute with static type
     *
     * @return array
     */
    protected function getEavColumns()
    {
        $columns = [];
        foreach ($this->getAttributes() as $attribute) {
            if ($attribute['backend_type'] == 'static') {
                continue;
            }
            $columns[$attribute['attribute_code']] = [];
            switch ($attribute['backend_type']) {
                case 'varchar':
                    $columns[$attribute['attribute_code']] = [
                        'type' => [\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255],
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label'],
                    ];
                    break;
                case 'int':
                    $columns[$attribute['attribute_code']] = [
                        'type' => [\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null],
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label'],
                    ];
                    break;
                case 'text':
                    $columns[$attribute['attribute_code']] = [
                        'type' => [\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k'],
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label'],
                    ];
                    break;
                case 'datetime':
                    $columns[$attribute['attribute_code']] = [
                        'type' => [\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null],
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label'],
                    ];
                    break;
                case 'decimal':
                    $columns[$attribute['attribute_code']] = [
                        'type' => [\Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '12,4'],
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label'],
                    ];
                    break;
            }
        }

        return $columns;
    }

    /**
     * Return array of attribute codes for entity type 'catalog_category'
     *
     * @return array
     */
    protected function getAttributes()
    {
        if ($this->attributeCodes === null) {
            $select = $this->connection->select()->from(
                $this->connection->getTableName($this->getTableName('eav_entity_type')),
                []
            )->join(
                $this->connection->getTableName($this->getTableName('eav_attribute')),
                $this->connection->getTableName(
                    $this->getTableName('eav_attribute')
                ) . '.entity_type_id = ' . $this->connection->getTableName(
                    $this->getTableName('eav_entity_type')
                ) . '.entity_type_id',
                $this->connection->getTableName($this->getTableName('eav_attribute')) . '.*'
            )->where(
                $this->connection->getTableName(
                    $this->getTableName('eav_entity_type')
                ) . '.entity_type_code = ?',
                \Magento\Catalog\Model\Category::ENTITY
            );
            $this->attributeCodes = [];
            foreach ($this->connection->fetchAll($select) as $attribute) {
                $this->attributeCodes[$attribute['attribute_id']] = $attribute;
            }
        }

        return $this->attributeCodes;
    }

    /**
     * Return attribute values for given entities and store
     *
     * @param array $entityIds
     * @param integer $storeId
     * @return array
     */
    protected function getAttributeValues($entityIds, $storeId)
    {
        if (!is_array($entityIds)) {
            $entityIds = [$entityIds];
        }
        $values = [];

        $linkIds = $this->getLinkIds($entityIds);
        foreach ($linkIds as $linkId) {
            $values[$linkId] = [];
        }

        $attributes = $this->getAttributes();
        $attributesType = ['varchar', 'int', 'decimal', 'text', 'datetime'];
        $linkField = $this->getCategoryMetadata()->getLinkField();
        foreach ($attributesType as $type) {
            foreach ($this->getAttributeTypeValues($type, $entityIds, $storeId) as $row) {
                if (isset($row[$linkField], $row['attribute_id'])) {
                    $attributeId = $row['attribute_id'];
                    if (isset($attributes[$attributeId])) {
                        $attributeCode = $attributes[$attributeId]['attribute_code'];
                        $values[$row[$linkField]][$attributeCode] = $row['value'];
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Translate entity ids into link ids
     *
     * Used for rows with no EAV attributes set.
     *
     * @param array $entityIds
     * @return array
     */
    private function getLinkIds(array $entityIds)
    {
        $linkField = $this->getCategoryMetadata()->getLinkField();
        if ($linkField === 'entity_id') {
            return $entityIds;
        }

        $select = $this->connection->select()->from(
            ['e' => $this->connection->getTableName($this->getTableName('catalog_category_entity'))],
            [$linkField]
        )->where(
            'e.entity_id IN (?)',
            $entityIds
        );

        return $this->connection->fetchCol($select);
    }

    /**
     * Return attribute values for given entities and store of specific attribute type
     *
     * @param string $type
     * @param array $entityIds
     * @param integer $storeId
     * @return array
     */
    protected function getAttributeTypeValues($type, $entityIds, $storeId)
    {
        $linkField = $this->getCategoryMetadata()->getLinkField();
        $select = $this->connection->select()->from(
            [
                'def' => $this->connection->getTableName($this->getTableName('catalog_category_entity_' . $type)),
            ],
            [$linkField, 'attribute_id']
        )->joinLeft(
            [
                'e' => $this->connection->getTableName($this->getTableName('catalog_category_entity'))
            ],
            "def.{$linkField} = e.{$linkField}"
        )->joinLeft(
            [
                'store' => $this->connection->getTableName(
                    $this->getTableName('catalog_category_entity_' . $type)
                ),
            ],
            "store.{$linkField} = def.{$linkField} AND store.attribute_id = def.attribute_id " .
            'AND store.store_id = ' .
            $storeId,
            [
                'value' => $this->connection->getCheckSql(
                    'store.value_id > 0',
                    $this->connection->quoteIdentifier('store.value'),
                    $this->connection->quoteIdentifier('def.value')
                )
            ]
        )->where(
            "e.entity_id IN (?)",
            $entityIds
        )->where(
            'def.store_id IN (?)',
            [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeId]
        );

        return $this->connection->fetchAll($select);
    }

    /**
     * Prepare array of column and columnValue pairs
     *
     * @param array $data
     * @return array
     */
    protected function prepareValuesToInsert($data)
    {
        $values = [];
        foreach (array_keys($this->getColumns()) as $column) {
            if (isset($data[$column])) {
                $values[$column] = $data[$column];
            } else {
                $values[$column] = null;
            }
        }
        return $values;
    }

    /**
     * Get table name
     *
     * @param string $name
     * @return string
     */
    protected function getTableName($name)
    {
        return $this->resource->getTableName($name);
    }

    /**
     * Get category metadata instance.
     *
     * @return \Magento\Framework\EntityManager\EntityMetadata
     */
    private function getCategoryMetadata()
    {
        if (null === $this->categoryMetadata) {
            $metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
            $this->categoryMetadata = $metadataPool->getMetadata(\Magento\Catalog\Api\Data\CategoryInterface::class);
        }
        return $this->categoryMetadata;
    }

    /**
     * Get skip static columns instance.
     *
     * @return array
     */
    private function getSkipStaticColumns()
    {
        if (null === $this->skipStaticColumns) {
            $provider = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Model\Indexer\Category\Flat\SkipStaticColumnsProvider::class);
            $this->skipStaticColumns = $provider->get();
        }
        return $this->skipStaticColumns;
    }
}
