<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Product\Flat\Indexer;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db;

/**
 * Class for building flat index
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FlatTableBuilder
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * Path to maximum available amount of indexes for flat indexer
     */
    public const XML_NODE_MAX_INDEX_COUNT = 'catalog/product/flat/max_index_count';

    /**
     * @var Indexer
     */
    protected $_productIndexerHelper;

    /**
     * @var AdapterInterface
     */
    protected $_connection;

    /**
     * @var ScopeConfigInterface $config
     */
    protected $_config;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var TableDataInterface
     */
    protected $_tableData;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @param Indexer $productIndexerHelper
     * @param ResourceConnection $resource
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param TableDataInterface $tableData
     */
    public function __construct(
        Indexer $productIndexerHelper,
        ResourceConnection $resource,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        TableDataInterface $tableData
    ) {
        $this->_productIndexerHelper = $productIndexerHelper;
        $this->resource = $resource;
        $this->_connection = $resource->getConnection();
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
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _createTemporaryFlatTable($storeId)
    {
        $columns = $this->_productIndexerHelper->getFlatColumns();

        $indexesNeed = $this->_productIndexerHelper->getFlatIndexes();

        $maxIndex = $this->_config->getValue(
            self::XML_NODE_MAX_INDEX_COUNT
        );
        if ($maxIndex && count($indexesNeed) > $maxIndex) {
            throw new LocalizedException(
                __(
                    'The Flat Catalog module has a limit of %2$d filterable and/or sortable attributes.'
                    . 'Currently there are %1$d of them.'
                    . 'Please reduce the number of filterable/sortable attributes in order to use this module',
                    count($indexesNeed),
                    $maxIndex
                )
            );
        }

        $indexKeys = [];
        $indexProps = array_values($indexesNeed);
        $upperPrimaryKey = strtoupper(AdapterInterface::INDEX_TYPE_PRIMARY);
        foreach ($indexProps as $i => $indexProp) {
            $indexName = $this->_connection->getIndexName(
                $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($storeId)),
                $indexProp['fields'],
                $indexProp['type']
            );
            $indexProp['type'] = strtoupper($indexProp['type'] ?? '');
            if ($indexProp['type'] == $upperPrimaryKey) {
                $indexKey = $upperPrimaryKey;
            } else {
                $indexKey = $indexName;
            }

            $indexProps[$i] = [
                'KEY_NAME' => $indexName,
                'COLUMNS_LIST' => $indexProp['fields'],
                'INDEX_TYPE' => strtolower($indexProp['type']),
            ];
            $indexKeys[$i] = $indexKey;
        }
        $indexesNeed = array_combine($indexKeys, $indexProps);

        /** @var $table Table */
        $table = $this->_connection->newTable(
            $this->_getTemporaryTableName($this->_productIndexerHelper->getFlatTableName($storeId))
        );
        foreach ($columns as $fieldName => $fieldProp) {
            $columnLength = isset($fieldProp['length']) ? $fieldProp['length'] : null;

            $columnDefinition = [
                'nullable' => isset($fieldProp['nullable']) ? (bool)$fieldProp['nullable'] : false,
                'unsigned' => isset($fieldProp['unsigned']) ? (bool)$fieldProp['unsigned'] : false,
                'default' => isset($fieldProp['default']) ? $fieldProp['default'] : false,
                'primary' => false,
            ];

            $columnComment = isset($fieldProp['comment']) ? $fieldProp['comment'] : $fieldName;

            if ($fieldName == 'created_at') {
                $columnDefinition['nullable'] = true;
                $columnDefinition['default'] = null;
            }

            $table->addColumn($fieldName, $fieldProp['type'], $columnLength, $columnDefinition, $columnComment);
        }

        foreach ($indexesNeed as $indexProp) {
            $table->addIndex(
                $indexProp['KEY_NAME'],
                $indexProp['COLUMNS_LIST'],
                ['type' => $indexProp['INDEX_TYPE']]
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
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _fillTemporaryFlatTable(array $tables, $storeId, $valueFieldSuffix)
    {
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
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
        $allColumns = [];
        $allColumns[] = array_values(
            array_unique(
                array_merge(['entity_id', $linkField, 'type_id', 'attribute_set_id'], $columnsList)
            )
        );

        /* @var $status Attribute */
        $status = $this->_productIndexerHelper->getAttribute('status');
        $statusTable = $this->_getTemporaryTableName($status->getBackendTable());
        $statusConditions = [
            sprintf('e.%s = dstatus.%s', $linkField, $linkField),
            'dstatus.store_id = ' . (int)$storeId,
            'dstatus.attribute_id = ' . (int)$status->getId(),
        ];
        $statusExpression = $this->_connection->getIfNullSql(
            'dstatus.value',
            $this->_connection->quoteIdentifier("{$statusTable}.status")
        );

        $select->from(
            ['et' => $entityTemporaryTableName],
            array_merge([], ...$allColumns)
        )->joinInner(
            ['e' => $this->resource->getTableName('catalog_product_entity')],
            'e.entity_id = et.entity_id',
            []
        )->joinInner(
            ['wp' => $this->_productIndexerHelper->getTable('catalog_product_website')],
            'wp.product_id = e.entity_id AND wp.website_id = ' . $websiteId,
            []
        )->joinLeft(
            ['dstatus' => $status->getBackend()->getTable()],
            implode(' AND ', $statusConditions),
            []
        )->where(
            $statusExpression . ' = ' . Status::STATUS_ENABLED
        );

        foreach ($tables as $tableName => $columns) {
            $columnValueNames = [];
            $temporaryTableName = $this->_getTemporaryTableName($tableName);
            $temporaryValueTableName = $temporaryTableName . $valueFieldSuffix;
            $columnsNames = array_keys($columns);

            $select->joinLeft(
                $temporaryTableName,
                sprintf('e.%1$s = %2$s.%1$s', $linkField, $temporaryTableName),
                $columnsNames
            );
            $allColumns[] = $columnsNames;

            foreach ($columnsNames as $name) {
                $columnValueName = $name . $valueFieldSuffix;
                if (isset($flatColumns[$columnValueName])) {
                    $columnValueNames[] = $columnValueName;
                }
            }
            if (!empty($columnValueNames)) {
                $select->joinLeft(
                    $temporaryValueTableName,
                    sprintf('e.%1$s = %2$s.%1$s', $linkField, $temporaryValueTableName),
                    $columnValueNames
                );
                $allColumns[] = $columnValueNames;
            }
        }
        $sql = $select->insertFromSelect($temporaryFlatTableName, array_merge([], ...$allColumns), false);
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
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        foreach ($tables as $tableName => $columns) {
            foreach ($columns as $attribute) {
                /* @var $attribute Attribute */
                $attributeCode = $attribute->getAttributeCode();
                if ($attribute->getBackend()->getType() != 'static') {
                    $joinCondition = sprintf('t.%s = e.%s', $linkField, $linkField) .
                        ' AND t.attribute_id=' .
                        $attribute->getId() .
                        ' AND t.store_id = ' .
                        $storeId .
                        ' AND t.value IS NOT NULL';
                    /** @var $select Select */
                    $select = $this->_connection->select()
                        ->joinInner(
                            ['e' => $this->resource->getTableName('catalog_product_entity')],
                            'e.entity_id = et.entity_id',
                            []
                        )->joinInner(
                            ['t' => $tableName],
                            $joinCondition,
                            [$attributeCode => 't.value']
                        );
                    if (!empty($changedIds)) {
                        $select->where(
                            $this->_connection->quoteInto('et.entity_id IN (?)', $changedIds, Zend_Db::INT_TYPE)
                        );
                    }
                    $sql = $select->crossUpdateFromSelect(['et' => $temporaryFlatTableName]);
                    $this->_connection->query($sql);
                }

                //Update not simple attributes (eg. dropdown)
                $columnName = $attributeCode . $valueFieldSuffix;
                if (isset($flatColumns[$columnName])) {
                    $columnValue = $this->_connection->getIfNullSql('ts.value', 't0.value');
                    $select = $this->_connection->select();
                    $select->joinLeft(
                        ['t0' => $this->_productIndexerHelper->getTable('eav_attribute_option_value')],
                        't0.option_id = et.' . $attributeCode . ' AND t0.store_id = 0',
                        []
                    )->joinLeft(
                        ['ts' => $this->_productIndexerHelper->getTable('eav_attribute_option_value')],
                        'ts.option_id = et.' . $attributeCode . ' AND ts.store_id = ' . $storeId,
                        []
                    )->columns(
                        [$columnName => $columnValue]
                    )->where($columnValue . ' IS NOT NULL');
                    if (!empty($changedIds)) {
                        $select->where(
                            $this->_connection->quoteInto(
                                'et.entity_id IN (?)',
                                $changedIds,
                                Zend_Db::INT_TYPE
                            )
                        );
                    }
                    $sql = $select->crossUpdateFromSelect(['et' => $temporaryFlatTableName]);
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

    /**
     * Get metadata pool
     *
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()
                ->get(MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
