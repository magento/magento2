<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Framework\StoreManagerInterface
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
    protected $columns = array();

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
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
                array(
                    'nullable' => $fieldProp['nullable'],
                    'unsigned' => $fieldProp['unsigned'],
                    'default' => $default,
                    'primary' => isset($fieldProp['primary']) ? $fieldProp['primary'] : false
                ),
                $fieldProp['comment'] != '' ? $fieldProp['comment'] : ucwords(str_replace('_', ' ', $fieldName))
            );
        }

        // Adding indexes
        $table->addIndex(
            $this->getWriteAdapter()->getIndexName($tableName, array('entity_id')),
            array('entity_id'),
            array('type' => 'primary')
        );
        $table->addIndex(
            $this->getWriteAdapter()->getIndexName($tableName, array('store_id')),
            array('store_id'),
            array('type' => 'index')
        );
        $table->addIndex(
            $this->getWriteAdapter()->getIndexName($tableName, array('path')),
            array('path'),
            array('type' => 'index')
        );
        $table->addIndex(
            $this->getWriteAdapter()->getIndexName($tableName, array('level')),
            array('level'),
            array('type' => 'index')
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
        $columns = array();
        $columnsToSkip = array('entity_type_id', 'attribute_set_id');
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
            $columns[$column['COLUMN_NAME']] = array(
                'type' => array($ddlType, $options),
                'unsigned' => $isUnsigned,
                'nullable' => $column['NULLABLE'],
                'default' => $column['DEFAULT'] === null ? false : $column['DEFAULT'],
                'comment' => $column['COLUMN_NAME']
            );
        }
        $columns['store_id'] = array(
            'type' => array(\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 5),
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
            'comment' => 'Store Id'
        );

        return $columns;
    }

    /**
     * Return array of eav columns, skip attribute with static type
     *
     * @return array
     */
    protected function getEavColumns()
    {
        $columns = array();
        foreach ($this->getAttributes() as $attribute) {
            if ($attribute['backend_type'] == 'static') {
                continue;
            }
            $columns[$attribute['attribute_code']] = array();
            switch ($attribute['backend_type']) {
                case 'varchar':
                    $columns[$attribute['attribute_code']] = array(
                        'type' => array(\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255),
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label']
                    );
                    break;
                case 'int':
                    $columns[$attribute['attribute_code']] = array(
                        'type' => array(\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null),
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label']
                    );
                    break;
                case 'text':
                    $columns[$attribute['attribute_code']] = array(
                        'type' => array(\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k'),
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label']
                    );
                    break;
                case 'datetime':
                    $columns[$attribute['attribute_code']] = array(
                        'type' => array(\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null),
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label']
                    );
                    break;
                case 'decimal':
                    $columns[$attribute['attribute_code']] = array(
                        'type' => array(\Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '12,4'),
                        'unsigned' => null,
                        'nullable' => true,
                        'default' => null,
                        'comment' => (string)$attribute['frontend_label']
                    );
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
                array()
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
            $this->attributeCodes = array();
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
            $entityIds = array($entityIds);
        }
        $values = array();

        foreach ($entityIds as $entityId) {
            $values[$entityId] = array();
        }
        $attributes = $this->getAttributes();
        $attributesType = array('varchar', 'int', 'decimal', 'text', 'datetime');
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
            array(
                'def' => $this->getReadAdapter()->getTableName($this->getTableName('catalog_category_entity_' . $type))
            ),
            array('entity_id', 'attribute_id')
        )->joinLeft(
            array(
                'store' => $this->getReadAdapter()->getTableName(
                    $this->getTableName('catalog_category_entity_' . $type)
                )
            ),
            'store.entity_id = def.entity_id AND store.attribute_id = def.attribute_id ' .
            'AND store.store_id = ' .
            $storeId,
            array(
                'value' => $this->getReadAdapter()->getCheckSql(
                    'store.value_id > 0',
                    $this->getReadAdapter()->quoteIdentifier('store.value'),
                    $this->getReadAdapter()->quoteIdentifier('def.value')
                )
            )
        )->where(
            'def.entity_id IN (?)',
            $entityIds
        )->where(
            'def.store_id IN (?)',
            array(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeId)
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
        $values = array();
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
