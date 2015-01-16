<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Flat;

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
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\Resource\Helper
     */
    protected $resourceHelper;

    /**
     * Flat columns
     *
     * @var array
     */
    protected $columns = [];

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper
    ) {
        $this->resource = $resource;
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
            $storeId = intval($storeId);
        }

        $suffix = sprintf('store_%d', $storeId);
        $table = $this->getWriteAdapter()->getTableName($this->getTableName('catalog_category_flat_' . $suffix));

        return $table;
    }

    /**
     * Retrieve connection for read data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getReadAdapter()
    {
        $writeAdapter = $this->getWriteAdapter();
        if ($writeAdapter && $writeAdapter->getTransactionLevel() > 0) {
            // if transaction is started we should use write connection for reading
            return $writeAdapter;
        }
        return $this->resource->getConnection('read');
    }

    /**
     * Retrieve connection for write data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getWriteAdapter()
    {
        return $this->resource->getConnection('write');
    }

    /**
     * Return structure for flat catalog table
     *
     * @param string $tableName
     * @return \Magento\Framework\DB\Ddl\Table
     */
    protected function getFlatTableStructure($tableName)
    {
        $table = $this->getWriteAdapter()->newTable(
            $tableName
        )->setComment(
            sprintf("Catalog Category Flat", $tableName)
        );

        //Adding columns
        foreach ($this->getColumns() as $fieldName => $fieldProp) {
            $default = $fieldProp['default'];
            if ($fieldProp['type'][0] == \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP && $default == 'CURRENT_TIMESTAMP') {
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
            $this->getWriteAdapter()->getIndexName($tableName, ['entity_id']),
            ['entity_id'],
            ['type' => 'primary']
        );
        $table->addIndex(
            $this->getWriteAdapter()->getIndexName($tableName, ['store_id']),
            ['store_id'],
            ['type' => 'index']
        );
        $table->addIndex(
            $this->getWriteAdapter()->getIndexName($tableName, ['path']),
            ['path'],
            ['type' => 'index']
        );
        $table->addIndex(
            $this->getWriteAdapter()->getIndexName($tableName, ['level']),
            ['level'],
            ['type' => 'index']
        );

        return $table;
    }

    /**
     * Return array of static columns
     *
     * @return array
     */
    protected function getStaticColumns()
    {
        $columns = [];
        $columnsToSkip = ['entity_type_id', 'attribute_set_id'];
        $describe = $this->getReadAdapter()->describeTable(
            $this->getReadAdapter()->getTableName($this->getTableName('catalog_category_entity'))
        );

        foreach ($describe as $column) {
            if (in_array($column['COLUMN_NAME'], $columnsToSkip)) {
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
            $select = $this->getReadAdapter()->select()->from(
                $this->getReadAdapter()->getTableName($this->getTableName('eav_entity_type')),
                []
            )->join(
                $this->getReadAdapter()->getTableName($this->getTableName('eav_attribute')),
                $this->getReadAdapter()->getTableName(
                    $this->getTableName('eav_attribute')
                ) . '.entity_type_id = ' . $this->getReadAdapter()->getTableName(
                    $this->getTableName('eav_entity_type')
                ) . '.entity_type_id',
                $this->getReadAdapter()->getTableName($this->getTableName('eav_attribute')) . '.*'
            )->where(
                $this->getReadAdapter()->getTableName(
                    $this->getTableName('eav_entity_type')
                ) . '.entity_type_code = ?',
                \Magento\Catalog\Model\Category::ENTITY
            );
            $this->attributeCodes = [];
            foreach ($this->getReadAdapter()->fetchAll($select) as $attribute) {
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

        foreach ($entityIds as $entityId) {
            $values[$entityId] = [];
        }
        $attributes = $this->getAttributes();
        $attributesType = ['varchar', 'int', 'decimal', 'text', 'datetime'];
        foreach ($attributesType as $type) {
            foreach ($this->getAttributeTypeValues($type, $entityIds, $storeId) as $row) {
                if (isset($row['entity_id']) && isset($row['attribute_id'])) {
                    $attributeId = $row['attribute_id'];
                    if (isset($attributes[$attributeId])) {
                        $attributeCode = $attributes[$attributeId]['attribute_code'];
                        $values[$row['entity_id']][$attributeCode] = $row['value'];
                    }
                }
            }
        }
        return $values;
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
        $select = $this->getReadAdapter()->select()->from(
            [
                'def' => $this->getReadAdapter()->getTableName($this->getTableName('catalog_category_entity_' . $type)),
            ],
            ['entity_id', 'attribute_id']
        )->joinLeft(
            [
                'store' => $this->getReadAdapter()->getTableName(
                    $this->getTableName('catalog_category_entity_' . $type)
                ),
            ],
            'store.entity_id = def.entity_id AND store.attribute_id = def.attribute_id ' .
            'AND store.store_id = ' .
            $storeId,
            [
                'value' => $this->getReadAdapter()->getCheckSql(
                    'store.value_id > 0',
                    $this->getReadAdapter()->quoteIdentifier('store.value'),
                    $this->getReadAdapter()->quoteIdentifier('def.value')
                )
            ]
        )->where(
            'def.entity_id IN (?)',
            $entityIds
        )->where(
            'def.store_id IN (?)',
            [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeId]
        );

        return $this->getReadAdapter()->fetchAll($select);
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
}
