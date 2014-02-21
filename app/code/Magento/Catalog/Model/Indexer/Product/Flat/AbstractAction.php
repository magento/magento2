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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

/**
 * Abstract action reindex class
 *
 * @package Magento\Catalog\Model\Indexer\Product\Flat
 */
abstract class AbstractAction
{
    /**
     * Path to maximum available amount of indexes for flat indexer
     */
    const XML_NODE_MAX_INDEX_COUNT  = 'catalog/product/flat/max_index_count';

    /**
     * Maximum size of attributes chunk
     */
    const ATTRIBUTES_CHUNK_SIZE = 59;

    /**
     * Suffix for value field on composite attributes
     *
     * @var string
     */
    protected $_valueFieldSuffix = '_value';

    /**
     * Logger instance
     *
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * Resource instance
     *
     * @var \Magento\App\Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $_coreStoreConfig;

    /**
     * Current store number representation
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Suffix for drop table (uses on flat table rename)
     *
     * @var string
     */
    protected $_tableDropSuffix = '_drop_indexer';

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $_productIndexerHelper;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * Product flat helper
     *
     * @var \Magento\Catalog\Helper\Product\Flat
     */
    protected $_productFlatHelper;

    /**
     * @var \Magento\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productType;

    /**
     * Existing flat tables flags pool
     *
     * @var array
     */
    protected $_flatTablesExist = array();

    /**
     * Contains list of created "value" tables
     *
     * @var array
     */
    protected $_valueTables = array();

    /**
     * List of product types available in installation
     *
     * @var array
     */
    protected $_productTypes = array();

    /**
     * Calls amount during current session
     *
     * @var int
     */
    protected static $_calls = 0;

    /**
     * @var \Magento\App\ConfigInterface $config
     */
    protected $_config;

    /**
     * @var \Magento\Catalog\Helper\Product\Flat
     */
    protected $_flatProductHelper;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_flatProductProcessor;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\App\Resource $resource
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Magento\App\ConfigInterface $config
     * @param \Magento\Catalog\Helper\Product\Flat $flatProductHelper
     * @param Processor $flatProductProcessor
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\App\Resource $resource,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productHelper,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\App\ConfigInterface $config,
        \Magento\Catalog\Helper\Product\Flat $flatProductHelper,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $flatProductProcessor
    ) {
        $this->_logger = $logger;
        $this->_resource = $resource;
        $this->_storeManager = $storeManager;
        $this->_resourceHelper = $resourceHelper;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_productIndexerHelper = $productHelper;
        $this->_productType = $productType;
        $this->_config = $config;
        $this->_connection = $resource->getConnection('default');
        $this->_flatProductHelper = $flatProductHelper;
        $this->_flatProductProcessor = $flatProductProcessor;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    abstract public function execute($ids);

    /**
     * Retrieve Catalog Product Flat Table name
     *
     * @param int $storeId
     * @return string
     */
    public function getFlatTableName($storeId)
    {
        return sprintf('%s_%s', $this->_connection->getTableName('catalog_product_flat'), $storeId);
    }

    /**
     * Return temporary table name by regular table name
     *
     * @param string $tableName
     *
     * @return string
     */
    protected function _getTemporaryTableName($tableName)
    {
        return sprintf('%s_tmp_indexer', $tableName);
    }

    /**
     * Create empty temporary table with given columns list
     *
     * @param string $tableName  Table name
     * @param array $columns array('columnName' => \Magento\Catalog\Model\Resource\Eav\Attribute, ...)
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _createTemporaryTable($tableName, array $columns)
    {
        if (!empty($columns)) {
            $valueTableName      = $tableName . $this->_valueFieldSuffix;
            $temporaryTable      = $this->_connection->newTable($tableName);
            $valueTemporaryTable = $this->_connection->newTable($valueTableName);
            $flatColumns         = $this->_productIndexerHelper->getFlatColumns();

            $temporaryTable->addColumn(
                'entity_id',
                \Magento\DB\Ddl\Table::TYPE_INTEGER
            );

            $temporaryTable->addColumn(
                'type_id',
                \Magento\DB\Ddl\Table::TYPE_TEXT
            );

            $temporaryTable->addColumn(
                'attribute_set_id',
                \Magento\DB\Ddl\Table::TYPE_INTEGER
            );

            $valueTemporaryTable->addColumn(
                'entity_id',
                \Magento\DB\Ddl\Table::TYPE_INTEGER
            );

            /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
            foreach ($columns as $columnName => $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                if (isset($flatColumns[$attributeCode])) {
                    $column = $flatColumns[$attributeCode];
                } else {
                    $column = $attribute->_getFlatColumnsDdlDefinition();
                    $column = $column[$attributeCode];
                }

                $temporaryTable->addColumn(
                    $columnName,
                    $column['type'],
                    isset($column['length']) ? $column['length'] : null
                );

                $columnValueName = $attributeCode . $this->_valueFieldSuffix;
                if (isset($flatColumns[$columnValueName])) {
                    $columnValue = $flatColumns[$columnValueName];
                    $valueTemporaryTable->addColumn(
                        $columnValueName,
                        $columnValue['type'],
                        isset($columnValue['length']) ? $columnValue['length'] : null
                    );
                }
            }
            $this->_connection->dropTemporaryTable($tableName);
            $this->_connection->createTemporaryTable($temporaryTable);

            if (count($valueTemporaryTable->getColumns()) > 1) {
                $this->_connection->dropTemporaryTable($valueTableName);
                $this->_connection->createTemporaryTable($valueTemporaryTable);
                $this->_valueTables[$valueTableName] = $valueTableName;
            }
        }
        return $this;
    }

    /**
     * Fill temporary entity table
     *
     * @param string $tableName
     * @param array  $columns
     * @param array  $changedIds
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _fillTemporaryEntityTable($tableName, array $columns, array $changedIds = array())
    {
        if (!empty($columns)) {
            $select = $this->_connection->select();
            $temporaryEntityTable = $this->_getTemporaryTableName($tableName);
            $idsColumns = array(
                'entity_id',
                'type_id',
                'attribute_set_id',
            );

            $columns = array_merge($idsColumns, array_keys($columns));

            $select->from(array('e' => $tableName), $columns);
            $onDuplicate = false;
            if (!empty($changedIds)) {
                $select->where(
                    $this->_connection->quoteInto('e.entity_id IN (?)', $changedIds)
                );
                $onDuplicate = true;
            }
            $sql = $select->insertFromSelect($temporaryEntityTable, $columns, $onDuplicate);
            $this->_connection->query($sql);
        }

        return $this;
    }

    /**
     * Fill temporary table by data from products EAV attributes by type
     *
     * @param string $tableName
     * @param array  $tableColumns
     * @param array  $changedIds
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _fillTemporaryTable($tableName, array $tableColumns, array $changedIds)
    {
        if (!empty($tableColumns)) {

            $columnsChunks = array_chunk($tableColumns, self::ATTRIBUTES_CHUNK_SIZE, true);
            foreach ($columnsChunks as $columnsList) {
                $select                  = $this->_connection->select();
                $selectValue             = $this->_connection->select();
                $entityTableName         = $this->_getTemporaryTableName(
                    $this->_productIndexerHelper->getTable('catalog_product_entity')
                );
                $temporaryTableName      = $this->_getTemporaryTableName($tableName);
                $temporaryValueTableName = $temporaryTableName . $this->_valueFieldSuffix;
                $keyColumn               = array('entity_id');
                $columns                 = array_merge($keyColumn, array_keys($columnsList));
                $valueColumns            = $keyColumn;
                $flatColumns             = $this->_productIndexerHelper->getFlatColumns();
                $iterationNum            = 1;

                $select->from(
                    array('e' => $entityTableName),
                    $keyColumn
                );

                $selectValue->from(
                    array('e' => $temporaryTableName),
                    $keyColumn
                );

                /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
                foreach ($columnsList as $columnName => $attribute) {
                    $countTableName = 't' . $iterationNum++;
                    $joinCondition  = sprintf(
                        'e.entity_id = %1$s.entity_id AND %1$s.attribute_id = %2$d AND %1$s.store_id = 0',
                        $countTableName,
                        $attribute->getId()
                    );

                    $select->joinLeft(
                        array($countTableName => $tableName),
                        $joinCondition,
                        array($columnName => 'value')
                    );

                    if ($attribute->getFlatUpdateSelect($this->_storeId) instanceof \Magento\DB\Select) {
                        $attributeCode   = $attribute->getAttributeCode();
                        $columnValueName = $attributeCode . $this->_valueFieldSuffix;
                        if (isset($flatColumns[$columnValueName])) {
                            $valueJoinCondition = sprintf(
                                'e.%1$s = %2$s.option_id AND %2$s.store_id = 0',
                                $attributeCode,
                                $countTableName
                            );
                            $selectValue->joinLeft(
                                array($countTableName => $this->_productIndexerHelper->getTable('eav_attribute_option_value')),
                                $valueJoinCondition,
                                array($columnValueName => $countTableName . '.value')
                            );
                            $valueColumns[] = $columnValueName;
                        }
                    }
                }

                if (!empty($changedIds)) {
                    $select->where(
                        $this->_connection->quoteInto('e.entity_id IN (?)', $changedIds)
                    );
                }

                $sql = $select->insertFromSelect($temporaryTableName, $columns, true);
                $this->_connection->query($sql);

                if (count($valueColumns) > 1) {
                    if (!empty($changedIds)) {
                        $selectValue->where(
                            $this->_connection->quoteInto('e.entity_id IN (?)', $changedIds)
                        );
                    }
                    $sql = $selectValue->insertFromSelect($temporaryValueTableName, $valueColumns, true);
                    $this->_connection->query($sql);
                }
            }
        }

        return $this;
    }

    /**
     * Add primary key to table by it name
     *
     * @param string $tableName
     * @param string $columnName
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _addPrimaryKeyToTable($tableName, $columnName = 'entity_id')
    {
        $this->_connection->addIndex(
            $tableName,
            'entity_id',
            array($columnName),
            \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY
        );

        return $this;
    }

    /**
     * Prepare flat table for store
     *
     * @throws \Magento\Core\Exception
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _createTemporaryFlatTable()
    {
        $columns = $this->_productIndexerHelper->getFlatColumns();

        $indexesNeed  = $this->_productIndexerHelper->getFlatIndexes();

        $maxIndex = $this->_config->getValue(self::XML_NODE_MAX_INDEX_COUNT);
        if ($maxIndex && count($indexesNeed) > $maxIndex) {
            throw new \Magento\Core\Exception(
                __("The Flat Catalog module has a limit of %2\$d filterable and/or sortable attributes."
                . "Currently there are %1\$d of them."
                . "Please reduce the number of filterable/sortable attributes in order to use this module",
                    count($indexesNeed), $maxIndex)
            );
        }

        $indexKeys = array();
        $indexProps = array_values($indexesNeed);
        $upperPrimaryKey = strtoupper(\Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY);
        foreach ($indexProps as $i => $indexProp) {
            $indexName = $this->_connection->getIndexName(
                $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($this->_storeId)),
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
                'KEY_NAME'     => $indexName,
                'COLUMNS_LIST' => $indexProp['fields'],
                'INDEX_TYPE'   => strtolower($indexProp['type'])
            );
            $indexKeys[$i] = $indexKey;
        }
        $indexesNeed = array_combine($indexKeys, $indexProps);

        /** @var $table \Magento\DB\Ddl\Table */
        $table = $this->_connection->newTable(
            $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($this->_storeId))
        );
        foreach ($columns as $fieldName => $fieldProp) {
            $columnLength = isset($fieldProp['length']) ? $fieldProp['length'] : null;

            $columnDefinition = array(
                'nullable' => isset($fieldProp['nullable']) ? (bool)$fieldProp['nullable'] : false,
                'unsigned' => isset($fieldProp['unsigned']) ? (bool)$fieldProp['unsigned'] : false,
                'default'  => isset($fieldProp['default']) ? $fieldProp['default'] : false,
                'primary'  => false,
            );

            $columnComment = isset($fieldProp['comment']) ? $fieldProp['comment'] : $fieldName;

            $table->addColumn(
                $fieldName,
                $fieldProp['type'],
                $columnLength,
                $columnDefinition,
                $columnComment
            );
        }

        foreach ($indexesNeed as $indexProp) {
            $table->addIndex(
                $indexProp['KEY_NAME'], $indexProp['COLUMNS_LIST'],
                array('type' => $indexProp['INDEX_TYPE'])
            );
        }

        $table->setComment("Catalog Product Flat (Store {$this->_storeId})");

        $this->_connection->dropTable(
            $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($this->_storeId))
        );
        $this->_connection->createTable($table);

        return $this;
    }

    /**
     * Fill temporary flat table by data from temporary flat table parts
     *
     * @param array $tables
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _fillTemporaryFlatTable(array $tables)
    {
        $select                   = $this->_connection->select();
        $temporaryFlatTableName   = $this->_getTemporaryTableName(
            $this->_productIndexerHelper->getFlatTableName($this->_storeId)
        );
        $flatColumns              = $this->_productIndexerHelper->getFlatColumns();
        $entityTableName          = $this->_productIndexerHelper->getTable('catalog_product_entity');
        $entityTemporaryTableName = $this->_getTemporaryTableName($entityTableName);
        $columnsList              = array_keys($tables[$entityTableName]);
        $websiteId                = (int)$this->_storeManager->getStore($this->_storeId)->getWebsiteId();

        unset($tables[$entityTableName]);

        $allColumns = array_merge(
            array(
                'entity_id',
                'type_id',
                'attribute_set_id',
            ),
            $columnsList
        );

        /* @var $status \Magento\Eav\Model\Entity\Attribute */
        $status = $this->_productIndexerHelper->getAttribute('status');
        $statusTable = $this->_getTemporaryTableName($status->getBackendTable());
        $statusConditions = array('e.entity_id = dstatus.entity_id',
            'dstatus.entity_type_id = ' . (int)$status->getEntityTypeId(), 'dstatus.store_id = ' . (int)$this->_storeId,
            'dstatus.attribute_id = ' . (int)$status->getId());
        $statusExpression = $this->_connection->getIfNullSql('dstatus.value',
            $this->_connection->quoteIdentifier("$statusTable.status"));

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
            $columnValueNames        = array();
            $temporaryTableName      = $this->_getTemporaryTableName($tableName);
            $temporaryValueTableName = $temporaryTableName . $this->_valueFieldSuffix;
            $columnsNames            = array_keys($columns);

            $select->joinLeft(
                $temporaryTableName,
                'e.entity_id = ' . $temporaryTableName. '.entity_id',
                $columnsNames
            );
            $allColumns = array_merge($allColumns, $columnsNames);

            foreach ($columnsNames as $name ) {
                $columnValueName = $name . $this->_valueFieldSuffix;
                if (isset($flatColumns[$columnValueName])) {
                    $columnValueNames[] = $columnValueName;
                }
            }
            if (!empty($columnValueNames)) {
                $select->joinLeft(
                    $temporaryValueTableName,
                    'e.entity_id = ' . $temporaryValueTableName. '.entity_id',
                    $columnValueNames
                );
                $allColumns = array_merge($allColumns, $columnValueNames);
            }
        }
        $sql = $select->insertFromSelect($temporaryFlatTableName, $allColumns, false);
        $this->_connection->query($sql);

        return $this;
    }

    /**
     * Apply diff. between 0 store and current store to temporary flat table
     *
     * @param array $tables
     * @param array $changedIds
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _updateTemporaryTableByStoreValues(array $tables, array $changedIds)
    {
        $flatColumns = $this->_productIndexerHelper->getFlatColumns();
        $temporaryFlatTableName = $this->_getTemporaryTableName(
            $this->_productIndexerHelper->getFlatTableName($this->_storeId)
        );

        foreach ($tables as $tableName => $columns) {
            foreach ($columns as $attribute) {
                /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $attributeCode = $attribute->getAttributeCode();
                if ($attribute->getBackend()->getType() != 'static') {
                    $joinCondition = 't.entity_id = e.entity_id'
                        . ' AND t.entity_type_id = ' . $attribute->getEntityTypeId()
                        . ' AND t.attribute_id=' . $attribute->getId()
                        . ' AND t.store_id = ' . $this->_storeId
                        . ' AND t.value IS NOT NULL';
                    /** @var $select \Magento\DB\Select */
                    $select = $this->_connection->select()
                        ->joinInner(
                            array('t' => $tableName),
                            $joinCondition,
                            array($attributeCode => 't.value')
                        );
                    if (!empty($changedIds)) {
                        $select->where(
                            $this->_connection->quoteInto('e.entity_id IN (?)', $changedIds)
                        );
                    }
                    $sql = $select->crossUpdateFromSelect(array('e' => $temporaryFlatTableName));
                    $this->_connection->query($sql);
                }

                //Update not simple attributes (eg. dropdown)
                if (isset($flatColumns[$attributeCode . $this->_valueFieldSuffix])) {
                    $select = $this->_connection->select()
                        ->joinInner(
                            array('t' => $this->_productIndexerHelper->getTable('eav_attribute_option_value')),
                            't.option_id = e.' . $attributeCode . ' AND t.store_id=' . $this->_storeId,
                            array($attributeCode . $this->_valueFieldSuffix => 't.value')
                        );
                    if (!empty($changedIds)) {
                        $select->where(
                            $this->_connection->quoteInto('e.entity_id IN (?)', $changedIds)
                        );
                    }
                    $sql = $select->crossUpdateFromSelect(array('e' => $temporaryFlatTableName));
                    $this->_connection->query($sql);
                }
            }
        }

        return $this;
    }

    /**
     * Swap flat product table and temporary flat table and drop old one
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _moveDataToFlatTable()
    {
        $flatTable              = $this->_productIndexerHelper->getFlatTableName($this->_storeId);
        $flatDropName           = $flatTable . $this->_tableDropSuffix;
        $temporaryFlatTableName = $this->_getTemporaryTableName(
            $this->_productIndexerHelper->getFlatTableName($this->_storeId)
        );
        $renameTables           = array();

        if ($this->_connection->isTableExists($flatTable)) {
            $renameTables[] = array(
                'oldName' => $flatTable,
                'newName' => $flatDropName,
            );
        }
        $renameTables[] = array(
            'oldName' => $temporaryFlatTableName,
            'newName' => $flatTable,
        );

        $this->_connection->dropTable($flatDropName);
        $this->_connection->renameTablesBatch($renameTables);
        $this->_connection->dropTable($flatDropName);

        return $this;
    }

    /**
     * Drop temporary tables created by reindex process
     *
     * @param array $tablesList
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _cleanOnFailure(array $tablesList)
    {
        foreach ($tablesList as $table => $columns) {
            $this->_connection->dropTemporaryTable($table);
        }
        $tableName = $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($this->_storeId));
        $this->_connection->dropTable($tableName);
        return $this;
    }

    /**
     * Rebuild catalog flat index from scratch
     *
     * @param int $storeId
     * @param array $changedIds
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     * @throws \Exception
     */
    protected function _reindex($storeId, array $changedIds = array())
    {
        $this->_storeId     = $storeId;
        $entityTableName    = $this->_productIndexerHelper->getTable('catalog_product_entity');
        $attributes         = $this->_productIndexerHelper->getAttributes();
        $eavAttributes      = $this->_productIndexerHelper->getTablesStructure($attributes);
        $entityTableColumns = $eavAttributes[$entityTableName];

        try {
            //We should prepare temp. tables only for first call of reindex all
            if (!self::$_calls) {
                $temporaryEavAttributes = $eavAttributes;

                //add status global value to the base table
                /* @var $status \Magento\Eav\Model\Entity\Attribute */
                $status = $this->_productIndexerHelper->getAttribute('status');
                $temporaryEavAttributes[$status->getBackendTable()]['status'] = $status;
                //Create list of temporary tables based on available attributes attributes
                foreach ($temporaryEavAttributes as $tableName => $columns) {
                    $this->_createTemporaryTable($this->_getTemporaryTableName($tableName), $columns);
                }

                //Fill "base" table which contains all available products
                $this->_fillTemporaryEntityTable($entityTableName, $entityTableColumns, $changedIds);

                //Add primary key to "base" temporary table for increase speed of joins in future
                $this->_addPrimaryKeyToTable($this->_getTemporaryTableName($entityTableName));
                unset($temporaryEavAttributes[$entityTableName]);

                foreach ($temporaryEavAttributes as $tableName => $columns) {
                    $temporaryTableName = $this->_getTemporaryTableName($tableName);

                    //Add primary key to temporary table for increase speed of joins in future
                    $this->_addPrimaryKeyToTable($temporaryTableName);

                    //Create temporary table for composite attributes
                    if (isset($this->_valueTables[$temporaryTableName . $this->_valueFieldSuffix])) {
                        $this->_addPrimaryKeyToTable($temporaryTableName . $this->_valueFieldSuffix);
                    }

                    //Fill temporary tables with attributes grouped by it type
                    $this->_fillTemporaryTable($tableName, $columns, $changedIds);
                }
            }
            //Create and fill flat temporary table
            $this->_createTemporaryFlatTable();
            $this->_fillTemporaryFlatTable($eavAttributes);
            //Update zero based attributes by values from current store
            $this->_updateTemporaryTableByStoreValues($eavAttributes, $changedIds);

            //Rename current flat table to "drop", rename temporary flat to flat and drop "drop" table
            $this->_moveDataToFlatTable();
            $this->_updateRelationProducts($this->_storeId, $changedIds);
            $this->_cleanRelationProducts($this->_storeId);
            self::$_calls++;
        } catch (\Exception $e) {
            $this->_cleanOnFailure($eavAttributes);
            throw $e;
        }

        return $this;
    }

    /**
     * Remove products from flat that are not exist
     *
     * @param array $ids
     * @param int $storeId
     */
    protected function _removeDeletedProducts(array &$ids, $storeId)
    {
        $select = $this->_connection->select()
            ->from($this->_productIndexerHelper->getTable('catalog_product_entity'))
            ->where('entity_id IN(?)', $ids);
        $result = $this->_connection->query($select);

        $existentProducts = [];
        foreach ($result->fetchAll()as $product) {
            $existentProducts[] = $product['entity_id'];
        }

        $productsToDelete = array_diff($ids, $existentProducts);
        $ids = $existentProducts;

        $this->deleteProductsFromStore($productsToDelete, $storeId);
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     */
    protected function _getProductTypeInstances()
    {
        if ($this->_productTypes === null) {
            $this->_productTypes = array();
            $productEmulator     = new \Magento\Object();
            foreach (array_keys($this->_productType->getTypes()) as $typeId) {
                $productEmulator->setTypeId($typeId);
                $this->_productTypes[$typeId] = $this->_productType->factory($productEmulator);
            }
        }
        return $this->_productTypes;
    }

    /**
     * Update relation products
     *
     * @param int $storeId
     * @param int|array $productIds Update child product(s) only
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _updateRelationProducts($storeId, $productIds = null)
    {
        if (!$this->_flatProductHelper->isAddChildData() || !$this->_isFlatTableExists($storeId)) {
            return $this;
        }

        foreach ($this->_getProductTypeInstances() as $typeInstance) {
            /** @var $typeInstance \Magento\Catalog\Model\Product\Type\AbstractType */
            if (!$typeInstance->isComposite(null)) {
                continue;
            }
            $relation = $typeInstance->getRelationInfo();
            if ($relation
                && $relation->getTable()
                && $relation->getParentFieldName()
                && $relation->getChildFieldName()
            ) {
                $columns   = $this->_productIndexerHelper->getFlatColumns();
                $fieldList = array_keys($columns);
                unset($columns['entity_id']);
                unset($columns['child_id']);
                unset($columns['is_child']);
                /** @var $select \Magento\DB\Select */
                $select = $this->_connection->select()
                    ->from(
                        array('t' => $this->_productIndexerHelper->getTable($relation->getTable())),
                        array($relation->getParentFieldName(), $relation->getChildFieldName(), new \Zend_Db_Expr('1')))
                    ->join(
                        array('e' => $this->_productIndexerHelper->getFlatTableName($storeId)),
                        "e.entity_id = t.{$relation->getChildFieldName()}",
                        array_keys($columns)
                    );
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                }
                if ($productIds !== null) {
                    $cond = array(
                        $this->_connection->quoteInto("{$relation->getChildFieldName()} IN(?)", $productIds),
                        $this->_connection->quoteInto("{$relation->getParentFieldName()} IN(?)", $productIds)
                    );

                    $select->where(implode(' OR ', $cond));
                }
                $sql = $select->insertFromSelect($this->_productIndexerHelper->getFlatTableName($storeId), $fieldList);
                $this->_connection->query($sql);
            }
        }

        return $this;
    }

    /**
     * Clean unused relation products
     *
     * @param int $storeId
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
     */
    protected function _cleanRelationProducts($storeId)
    {
        if (!$this->_flatProductHelper->isAddChildData()) {
            return $this;
        }

        foreach ($this->_getProductTypeInstances() as $typeInstance) {
            /** @var $typeInstance \Magento\Catalog\Model\Product\Type\AbstractType */
            if (!$typeInstance->isComposite(null)) {
                continue;
            }
            $relation = $typeInstance->getRelationInfo();
            if ($relation
                && $relation->getTable()
                && $relation->getParentFieldName()
                && $relation->getChildFieldName()
            ) {
                $select = $this->_connection->select()
                    ->distinct(true)
                    ->from(
                        $this->_productIndexerHelper->getTable($relation->getTable()),
                        "{$relation->getParentFieldName()}"
                    );
                $joinLeftCond = array(
                    "e.entity_id = t.{$relation->getParentFieldName()}",
                    "e.child_id = t.{$relation->getChildFieldName()}"
                );
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                    $joinLeftCond[] = $relation->getWhere();
                }

                $entitySelect = new \Zend_Db_Expr($select->__toString());
                /** @var $select \Magento\DB\Select */
                $select = $this->_connection->select()
                    ->from(array('e' => $this->_productIndexerHelper->getFlatTableName($storeId)), null)
                    ->joinLeft(
                        array('t' => $this->_productIndexerHelper->getTable($relation->getTable())),
                        implode(' AND ', $joinLeftCond),
                        array()
                    )
                    ->where('e.is_child = ?', 1)
                    ->where('e.entity_id IN(?)', $entitySelect)
                    ->where("t.{$relation->getChildFieldName()} IS NULL");

                $sql = $select->deleteFromSelect('e');
                $this->_connection->query($sql);
            }
        }

        return $this;
    }

    /**
     * Reindex single product into flat product table
     *
     * @param int $storeId
     * @param int $productId
     * @return \Magento\Catalog\Model\Indexer\Product\Flat
     */
    protected function _reindexSingleProduct($storeId, $productId)
    {
        $this->_storeId = $storeId;

        $flatTable = $this->_productIndexerHelper->getFlatTableName($this->_storeId);

        if (!$this->_connection->isTableExists($flatTable)) {
            $this->_createTemporaryFlatTable();
            $this->_moveDataToFlatTable();
        }

        $attributes    = $this->_productIndexerHelper->getAttributes();
        $eavAttributes = $this->_productIndexerHelper->getTablesStructure($attributes);
        $updateData    = array();
        $describe      = $this->_connection->describeTable($flatTable);

        foreach ($eavAttributes as $tableName => $tableColumns) {
            $columnsChunks = array_chunk($tableColumns, self::ATTRIBUTES_CHUNK_SIZE, true);

            foreach ($columnsChunks as $columns) {
                $select      = $this->_connection->select();
                $selectValue = $this->_connection->select();
                $keyColumns  = array(
                    'entity_id'    => 'e.entity_id',
                    'attribute_id' => 't.attribute_id',
                    'value'        =>  $this->_connection->getIfNullSql('`t2`.`value`', '`t`.`value`'),
                );

                if ($tableName != $this->_productIndexerHelper->getTable('catalog_product_entity')) {
                    $valueColumns = array();
                    $ids          = array();
                    $select->from(
                        array('e' => $this->_productIndexerHelper->getTable('catalog_product_entity')),
                        $keyColumns
                    );

                    $selectValue->from(
                        array('e' => $this->_productIndexerHelper->getTable('catalog_product_entity')),
                        $keyColumns
                    );

                    /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
                    foreach ($columns as $columnName => $attribute) {
                        if (isset($describe[$columnName])) {
                            $ids[$attribute->getId()] = $columnName;
                        }
                    }

                    $select->joinLeft(
                        array('t' => $tableName),
                        'e.entity_id = t.entity_id '
                        . $this->_connection->quoteInto(' AND t.attribute_id IN (?)', array_keys($ids))
                        . ' AND t.store_id = 0',
                        array()
                    )->joinLeft(
                            array('t2' => $tableName),
                            't.entity_id = t2.entity_id '
                            . ' AND t.attribute_id = t2.attribute_id  '
                            . $this->_connection->quoteInto(' AND t2.store_id = ?', $this->_storeId),
                            array()
                        )->where(
                            'e.entity_id = ' . $productId
                        )->where(
                            't.attribute_id IS NOT NULL'
                        );
                    $cursor = $this->_connection->query($select);
                    while ($row = $cursor->fetch(\Zend_Db::FETCH_ASSOC)) {
                        $updateData[$ids[$row['attribute_id']]] = $row['value'];
                        $valueColumnName = $ids[$row['attribute_id']] . $this->_valueFieldSuffix;
                        if (isset($describe[$valueColumnName])) {
                            $valueColumns[$row['value']] = $valueColumnName;
                        }
                    }

                    //Update not simple attributes (eg. dropdown)
                    if (!empty($valueColumns)) {
                        $valueIds = array_keys($valueColumns);

                        $select = $this->_connection->select()
                            ->from(
                                array('t' => $this->_productIndexerHelper->getTable('eav_attribute_option_value')),
                                array('t.option_id', 't.value')
                            )->where(
                                $this->_connection->quoteInto('t.option_id IN (?)', $valueIds)
                            );
                        $cursor = $this->_connection->query($select);
                        while ($row = $cursor->fetch(\Zend_Db::FETCH_ASSOC)) {
                            $valueColumnName = $valueColumns[$row['option_id']];
                            if (isset($describe[$valueColumnName])) {
                                $updateData[$valueColumnName] = $row['value'];
                            }
                        }
                    }

                } else {
                    $columnNames   = array_keys($columns);
                    $columnNames[] = 'attribute_set_id';
                    $columnNames[] = 'type_id';
                    $select->from(
                        array('e' => $this->_productIndexerHelper->getTable('catalog_product_entity')),
                        $columnNames
                    )->where(
                            'e.entity_id = ' . $productId
                        );
                    $cursor = $this->_connection->query($select);
                    $row    = $cursor->fetch(\Zend_Db::FETCH_ASSOC);
                    if (!empty($row)) {
                        foreach ($row as $columnName => $value) {
                            $updateData[$columnName] = $value;
                        }
                    }
                }
            }
        }

        if (!empty($updateData)) {
            $updateData += array('entity_id' => $productId);
            $updateFields = array();
            foreach ($updateData as $key => $value) {
                $updateFields[$key] = $key;
            }
            $this->_connection->insertOnDuplicate($flatTable, $updateData, $updateFields);
        }

        return $this;
    }

    /**
     * Delete products from flat table(s)
     *
     * @param int|array $productId
     * @param null|int $storeId
     */
    public function deleteProductsFromStore($productId, $storeId = null)
    {
        if (!is_array($productId)) {
            $productId = array($productId);
        }
        if (null === $storeId) {
            foreach ($this->_storeManager->getStores() as $store) {
                $this->_connection->delete(
                    $this->_productIndexerHelper->getFlatTableName($store->getId()),
                    array('entity_id IN(?)' => $productId)
                );
            }
        } else {
            $this->_connection->delete(
                $this->_productIndexerHelper->getFlatTableName((int)$storeId),
                array('entity_id IN(?)' => $productId)
            );
        }
    }

    /**
     * Check is flat table for store exists
     *
     * @param int $storeId
     * @return bool
     */
    protected function _isFlatTableExists($storeId)
    {
        if (!isset($this->_flatTablesExist[$storeId])) {
            $tableName     = $this->getFlatTableName($storeId);
            $isTableExists = $this->_connection->isTableExists($tableName);

            $this->_flatTablesExist[$storeId] = $isTableExists ? true : false;
        }

        return $this->_flatTablesExist[$storeId];
    }

    /**
     * set valid state
     *
     * @param $versionId
     */
    protected function _setValidState($versionId)
    {
        $this->_flatProductProcessor
            ->getIndexer()
            ->getView()
            ->getState()
            ->setStatus(\Magento\Indexer\Model\Indexer\State::STATUS_VALID)
            ->setVersionId($versionId)
            ->save();
        $this->_flatProductProcessor
            ->getIndexer()
            ->getView()
            ->getChangelog()
            ->clear($versionId);
    }

    /**
     * Set invalid state
     */
    protected function _setInvalidState()
    {
        $this->_flatProductProcessor->markIndexerAsInvalid();
    }

    /**
     * set processing state
     */
    protected function _setProcessingState()
    {
        $this->_flatProductProcessor
            ->getIndexer()
            ->getState()
            ->setStatus(\Magento\Indexer\Model\Indexer\State::STATUS_WORKING);
    }

    /**
     * Is indexer processing
     *
     * @return bool
     */
    protected function _isProcessing()
    {
        return $this->_flatProductProcessor->getIndexer()->isWorking();
    }
}
