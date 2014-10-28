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
namespace Magento\Catalog\Model\Indexer\Product\Flat;

/**
 * Class FlatTableBuilder
 */
class FlatTableBuilder
{
    /**
     * Path to maximum available amount of indexes for flat indexer
     */
    const XML_NODE_MAX_INDEX_COUNT = 'catalog/product/flat/max_index_count';

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $_productIndexerHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var TableDataInterface
     */
    protected $_tableData;

    /**
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productIndexerHelper
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param TableDataInterface $tableData
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\Flat\Indexer $productIndexerHelper,
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\TableDataInterface $tableData
    ) {
        $this->_productIndexerHelper = $productIndexerHelper;
        $this->_connection = $resource->getConnection('write');
        $this->_config = $config;
        $this->_storeManager = $storeManager;
        $this->_tableData = $tableData;
    }

    /**
     * Prepare temporary flat tables
     *
     * @param int|string $storeId
     * @param array $changedIds
     * @param string $valueFieldSuffix
     * @param string $tableDropSuffix
     * @param bool $fillTmpTables
     * @return void
     */
    public function build($storeId, $changedIds, $valueFieldSuffix, $tableDropSuffix, $fillTmpTables)
    {
        $attributes = $this->_productIndexerHelper->getAttributes();
        $eavAttributes = $this->_productIndexerHelper->getTablesStructure($attributes);


        $this->_createTemporaryFlatTable($storeId);

        if ($fillTmpTables) {
            $this->_fillTemporaryFlatTable($eavAttributes, $storeId, $valueFieldSuffix);
            //Update zero based attributes by values from current store
            $this->_updateTemporaryTableByStoreValues($eavAttributes, $changedIds, $storeId, $valueFieldSuffix);
        }

        $flatTable = $this->_productIndexerHelper->getFlatTableName($storeId);
        $flatDropName = $flatTable . $tableDropSuffix;
        $temporaryFlatTableName = $this->_getTemporaryTableName(
            $this->_productIndexerHelper->getFlatTableName($storeId)
        );
        $this->_tableData->move($flatTable, $flatDropName, $temporaryFlatTableName);
    }

    /**
     * Prepare flat table for store
     *
     * @param int|string $storeId
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _createTemporaryFlatTable($storeId)
    {
        $columns = $this->_productIndexerHelper->getFlatColumns();

        $indexesNeed = $this->_productIndexerHelper->getFlatIndexes();

        $maxIndex = $this->_config->getValue(
            self::XML_NODE_MAX_INDEX_COUNT
        );
        if ($maxIndex && count($indexesNeed) > $maxIndex) {
            throw new \Magento\Framework\Model\Exception(
                __(
                    "The Flat Catalog module has a limit of %2\$d filterable and/or sortable attributes." .
                    "Currently there are %1\$d of them." .
                    "Please reduce the number of filterable/sortable attributes in order to use this module",
                    count($indexesNeed),
                    $maxIndex
                )
            );
        }

        $indexKeys = array();
        $indexProps = array_values($indexesNeed);
        $upperPrimaryKey = strtoupper(\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY);
        foreach ($indexProps as $i => $indexProp) {
            $indexName = $this->_connection->getIndexName(
                $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($storeId)),
                $indexProp['fields'],
                $indexProp['type']
            );
            $indexProp['type'] = strtoupper($indexProp['type']);
            if ($indexProp['type'] == $upperPrimaryKey) {
                $indexKey = $upperPrimaryKey;
            } else {
                $indexKey = $indexName;
            }

            $indexProps[$i] = array(
                'KEY_NAME' => $indexName,
                'COLUMNS_LIST' => $indexProp['fields'],
                'INDEX_TYPE' => strtolower($indexProp['type'])
            );
            $indexKeys[$i] = $indexKey;
        }
        $indexesNeed = array_combine($indexKeys, $indexProps);

        /** @var $table \Magento\Framework\DB\Ddl\Table */
        $table = $this->_connection->newTable(
            $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($storeId))
        );
        foreach ($columns as $fieldName => $fieldProp) {
            $columnLength = isset($fieldProp['length']) ? $fieldProp['length'] : null;

            $columnDefinition = array(
                'nullable' => isset($fieldProp['nullable']) ? (bool)$fieldProp['nullable'] : false,
                'unsigned' => isset($fieldProp['unsigned']) ? (bool)$fieldProp['unsigned'] : false,
                'default' => isset($fieldProp['default']) ? $fieldProp['default'] : false,
                'primary' => false
            );

            $columnComment = isset($fieldProp['comment']) ? $fieldProp['comment'] : $fieldName;

            $table->addColumn($fieldName, $fieldProp['type'], $columnLength, $columnDefinition, $columnComment);
        }

        foreach ($indexesNeed as $indexProp) {
            $table->addIndex(
                $indexProp['KEY_NAME'],
                $indexProp['COLUMNS_LIST'],
                array('type' => $indexProp['INDEX_TYPE'])
            );
        }

        $table->setComment("Catalog Product Flat (Store {$storeId})");

        $this->_connection->dropTable(
            $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($storeId))
        );
        $this->_connection->createTable($table);
    }

    /**
     * Fill temporary flat table by data from temporary flat table parts
     *
     * @param array $tables
     * @param int|string $storeId
     * @param string $valueFieldSuffix
     * @return void
     */
    protected function _fillTemporaryFlatTable(array $tables, $storeId, $valueFieldSuffix)
    {
        $select = $this->_connection->select();
        $temporaryFlatTableName = $this->_getTemporaryTableName(
            $this->_productIndexerHelper->getFlatTableName($storeId)
        );
        $flatColumns = $this->_productIndexerHelper->getFlatColumns();
        $entityTableName = $this->_productIndexerHelper->getTable('catalog_product_entity');
        $entityTemporaryTableName = $this->_getTemporaryTableName($entityTableName);
        $columnsList = array_keys($tables[$entityTableName]);
        $websiteId = (int)$this->_storeManager->getStore($storeId)->getWebsiteId();

        unset($tables[$entityTableName]);

        $allColumns = array_merge(array('entity_id', 'type_id', 'attribute_set_id'), $columnsList);

        /* @var $status \Magento\Eav\Model\Entity\Attribute */
        $status = $this->_productIndexerHelper->getAttribute('status');
        $statusTable = $this->_getTemporaryTableName($status->getBackendTable());
        $statusConditions = array(
            'e.entity_id = dstatus.entity_id',
            'dstatus.entity_type_id = ' . (int)$status->getEntityTypeId(),
            'dstatus.store_id = ' . (int)$storeId,
            'dstatus.attribute_id = ' . (int)$status->getId()
        );
        $statusExpression = $this->_connection->getIfNullSql(
            'dstatus.value',
            $this->_connection->quoteIdentifier("{$statusTable}.status")
        );

        $select->from(
            array('e' => $entityTemporaryTableName),
            $allColumns
        )->joinInner(
            array('wp' => $this->_productIndexerHelper->getTable('catalog_product_website')),
            'wp.product_id = e.entity_id AND wp.website_id = ' . $websiteId,
            array()
        )->joinLeft(
            array('dstatus' => $status->getBackend()->getTable()),
            implode(' AND ', $statusConditions),
            array()
        )->where(
            $statusExpression . ' = ' . \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );

        foreach ($tables as $tableName => $columns) {
            $columnValueNames = array();
            $temporaryTableName = $this->_getTemporaryTableName($tableName);
            $temporaryValueTableName = $temporaryTableName . $valueFieldSuffix;
            $columnsNames = array_keys($columns);

            $select->joinLeft(
                $temporaryTableName,
                'e.entity_id = ' . $temporaryTableName . '.entity_id',
                $columnsNames
            );
            $allColumns = array_merge($allColumns, $columnsNames);

            foreach ($columnsNames as $name) {
                $columnValueName = $name . $valueFieldSuffix;
                if (isset($flatColumns[$columnValueName])) {
                    $columnValueNames[] = $columnValueName;
                }
            }
            if (!empty($columnValueNames)) {
                $select->joinLeft(
                    $temporaryValueTableName,
                    'e.entity_id = ' . $temporaryValueTableName . '.entity_id',
                    $columnValueNames
                );
                $allColumns = array_merge($allColumns, $columnValueNames);
            }
        }
        $sql = $select->insertFromSelect($temporaryFlatTableName, $allColumns, false);
        $this->_connection->query($sql);
    }

    /**
     * Apply diff. between 0 store and current store to temporary flat table
     *
     * @param array $tables
     * @param array $changedIds
     * @param int|string $storeId
     * @param string $valueFieldSuffix
     * @return void
     */
    protected function _updateTemporaryTableByStoreValues(
        array $tables,
        array $changedIds,
        $storeId,
        $valueFieldSuffix
    ) {
        $flatColumns = $this->_productIndexerHelper->getFlatColumns();
        $temporaryFlatTableName = $this->_getTemporaryTableName(
            $this->_productIndexerHelper->getFlatTableName($storeId)
        );

        foreach ($tables as $tableName => $columns) {
            foreach ($columns as $attribute) {
                /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $attributeCode = $attribute->getAttributeCode();
                if ($attribute->getBackend()->getType() != 'static') {
                    $joinCondition = 't.entity_id = e.entity_id' .
                        ' AND t.entity_type_id = ' .
                        $attribute->getEntityTypeId() .
                        ' AND t.attribute_id=' .
                        $attribute->getId() .
                        ' AND t.store_id = ' .
                        $storeId .
                        ' AND t.value IS NOT NULL';
                    /** @var $select \Magento\Framework\DB\Select */
                    $select = $this->_connection->select()->joinInner(
                        array('t' => $tableName),
                        $joinCondition,
                        array($attributeCode => 't.value')
                    );
                    if (!empty($changedIds)) {
                        $select->where($this->_connection->quoteInto('e.entity_id IN (?)', $changedIds));
                    }
                    $sql = $select->crossUpdateFromSelect(array('e' => $temporaryFlatTableName));
                    $this->_connection->query($sql);
                }

                //Update not simple attributes (eg. dropdown)
                if (isset($flatColumns[$attributeCode . $valueFieldSuffix])) {
                    $select = $this->_connection->select()->joinInner(
                        array('t' => $this->_productIndexerHelper->getTable('eav_attribute_option_value')),
                        't.option_id = e.' . $attributeCode . ' AND t.store_id=' . $storeId,
                        array($attributeCode . $valueFieldSuffix => 't.value')
                    );
                    if (!empty($changedIds)) {
                        $select->where($this->_connection->quoteInto('e.entity_id IN (?)', $changedIds));
                    }
                    $sql = $select->crossUpdateFromSelect(array('e' => $temporaryFlatTableName));
                    $this->_connection->query($sql);
                }
            }
        }
    }

    /**
     * Retrieve temporary table name by regular table name
     *
     * @param string $tableName
     * @return string
     */
    protected function _getTemporaryTableName($tableName)
    {
        return sprintf('%s_tmp_indexer', $tableName);
    }
}
