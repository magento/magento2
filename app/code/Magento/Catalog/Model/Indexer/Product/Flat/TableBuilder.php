<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

use Magento\Catalog\Model\Indexer\Product\Flat\Table\BuilderInterfaceFactory;

class TableBuilder
{
    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $_productIndexerHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var BuilderInterfaceFactory
     */
    private $tableBuilderFactory;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productIndexerHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param BuilderInterfaceFactory|null $tableBuilderFactory
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\Flat\Indexer $productIndexerHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        BuilderInterfaceFactory $tableBuilderFactory = null
    ) {
        $this->_productIndexerHelper = $productIndexerHelper;
        $this->resource = $resource;
        $this->_connection = $resource->getConnection();
        $this->tableBuilderFactory = $tableBuilderFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(BuilderInterfaceFactory::class);
    }

    /**
     * Prepare temporary tables only for first call of reindex all
     *
     * @param int $storeId
     * @param array $changedIds
     * @param string $valueFieldSuffix
     * @return void
     */
    public function build($storeId, $changedIds, $valueFieldSuffix)
    {
        $entityTableName = $this->_productIndexerHelper->getTable('catalog_product_entity');
        $attributes = $this->_productIndexerHelper->getAttributes();
        $eavAttributes = $this->_productIndexerHelper->getTablesStructure($attributes);
        $entityTableColumns = $eavAttributes[$entityTableName];
        $linkField = $this->getMetadataPool()
            ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();

        $temporaryEavAttributes = $eavAttributes;

        //add status global value to the base table
        /* @var $status \Magento\Eav\Model\Entity\Attribute */
        $status = $this->_productIndexerHelper->getAttribute('status');
        $temporaryEavAttributes[$status->getBackendTable()]['status'] = $status;
        //Create list of temporary tables based on available attributes attributes
        $valueTables = [];
        foreach ($temporaryEavAttributes as $tableName => $columns) {
            $valueTables = array_merge(
                $valueTables,
                $this->_createTemporaryTable($this->_getTemporaryTableName($tableName), $columns, $valueFieldSuffix)
            );
        }

        //Fill "base" table which contains all available products
        $this->_fillTemporaryEntityTable($entityTableName, $entityTableColumns, $changedIds);

        //Add primary key to "base" temporary table for increase speed of joins in future
        $this->_addPrimaryKeyToTable($this->_getTemporaryTableName($entityTableName));
        unset($temporaryEavAttributes[$entityTableName]);

        foreach ($temporaryEavAttributes as $tableName => $columns) {
            $temporaryTableName = $this->_getTemporaryTableName($tableName);

            //Add primary key to temporary table for increase speed of joins in future
            $this->_addPrimaryKeyToTable($temporaryTableName, $linkField);

            //Create temporary table for composite attributes
            if (isset($valueTables[$temporaryTableName . $valueFieldSuffix])) {
                $this->_addPrimaryKeyToTable($temporaryTableName . $valueFieldSuffix, $linkField);
            }

            //Fill temporary tables with attributes grouped by it type
            $this->_fillTemporaryTable($tableName, $columns, $changedIds, $valueFieldSuffix, $storeId);
        }
    }

    /**
     * Create empty temporary table with given columns list
     *
     * @param string $tableName  Table name
     * @param array $columns array('columnName' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute, ...)
     * @param string $valueFieldSuffix
     *
     * @return array
     */
    protected function _createTemporaryTable($tableName, array $columns, $valueFieldSuffix)
    {
        $valueTables = [];
        if (!empty($columns)) {
            $valueTableName = $tableName . $valueFieldSuffix;
            $temporaryTableBuilder = $this->tableBuilderFactory->create(
                [
                    'connection' => $this->_connection,
                    'tableName' => $tableName
                ]
            );
            $valueTemporaryTableBuilder = $this->tableBuilderFactory->create(
                [
                    'connection' => $this->_connection,
                    'tableName' => $valueTableName
                ]
            );
            $flatColumns = $this->_productIndexerHelper->getFlatColumns();

            $temporaryTableBuilder->addColumn('entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER);

            $temporaryTableBuilder->addColumn('type_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT);

            $temporaryTableBuilder->addColumn('attribute_set_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER);

            $valueTemporaryTableBuilder->addColumn('entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER);

            /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            foreach ($columns as $columnName => $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                if (isset($flatColumns[$attributeCode])) {
                    $column = $flatColumns[$attributeCode];
                } else {
                    $column = $attribute->_getFlatColumnsDdlDefinition();
                    $column = $column[$attributeCode];
                }

                $temporaryTableBuilder->addColumn(
                    $columnName,
                    $column['type'],
                    isset($column['length']) ? $column['length'] : null
                );

                $columnValueName = $attributeCode . $valueFieldSuffix;
                if (isset($flatColumns[$columnValueName])) {
                    $columnValue = $flatColumns[$columnValueName];
                    $valueTemporaryTableBuilder->addColumn(
                        $columnValueName,
                        $columnValue['type'],
                        isset($columnValue['length']) ? $columnValue['length'] : null
                    );
                }
            }
            $this->_connection->dropTemporaryTable($tableName);
            $this->_connection->createTemporaryTable($temporaryTableBuilder->getTable());

            if (count($valueTemporaryTableBuilder->getTable()->getColumns()) > 1) {
                $this->_connection->dropTemporaryTable($valueTableName);
                $this->_connection->createTemporaryTable($valueTemporaryTableBuilder->getTable());
                $valueTables[$valueTableName] = $valueTableName;
            }
        }
        return $valueTables;
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
     * Fill temporary entity table
     *
     * @param string $tableName
     * @param array  $columns
     * @param array  $changedIds
     * @return void
     */
    protected function _fillTemporaryEntityTable($tableName, array $columns, array $changedIds = [])
    {
        if (!empty($columns)) {
            $select = $this->_connection->select();
            $temporaryEntityTable = $this->_getTemporaryTableName($tableName);
            $metadata = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
            $idsColumns = array_unique([$metadata->getLinkField(), 'entity_id', 'type_id', 'attribute_set_id']);

            $columns = array_merge($idsColumns, array_keys($columns));

            $select->from(['e' => $tableName], $columns);
            $onDuplicate = false;
            if (!empty($changedIds)) {
                $select->where($this->_connection->quoteInto('e.entity_id IN (?)', $changedIds));
                $onDuplicate = true;
            }
            $sql = $select->insertFromSelect($temporaryEntityTable, $columns, $onDuplicate);
            $this->_connection->query($sql);
        }
    }

    /**
     * Add primary key to table by it name
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function _addPrimaryKeyToTable($tableName, $columnName = 'entity_id')
    {
        $this->_connection->addIndex(
            $tableName,
            'entity_id',
            [$columnName],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY
        );
    }

    /**
     * Fill temporary table by data from products EAV attributes by type
     *
     * @param string $tableName
     * @param array  $tableColumns
     * @param array  $changedIds
     * @param string $valueFieldSuffix
     * @param int $storeId
     * @return void
     */
    protected function _fillTemporaryTable(
        $tableName,
        array $tableColumns,
        array $changedIds,
        $valueFieldSuffix,
        $storeId
    ) {
        $metadata = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        if (!empty($tableColumns)) {
            $columnsChunks = array_chunk(
                $tableColumns,
                Action\Indexer::ATTRIBUTES_CHUNK_SIZE,
                true
            );
            foreach ($columnsChunks as $columnsList) {
                $select = $this->_connection->select();
                $selectValue = $this->_connection->select();
                $entityTableName = $this->_getTemporaryTableName(
                    $this->_productIndexerHelper->getTable('catalog_product_entity')
                );
                $temporaryTableName = $this->_getTemporaryTableName($tableName);
                $temporaryValueTableName = $temporaryTableName . $valueFieldSuffix;
                $keyColumn = array_unique([$metadata->getLinkField(), 'entity_id']);
                $columns = array_merge($keyColumn, array_keys($columnsList));
                $valueColumns = $keyColumn;
                $flatColumns = $this->_productIndexerHelper->getFlatColumns();
                $iterationNum = 1;

                $select->from(['et' => $entityTableName], $keyColumn)
                    ->join(
                        ['e' => $this->resource->getTableName('catalog_product_entity')],
                        'e.entity_id = et.entity_id',
                        []
                    );

                $selectValue->from(['e' => $temporaryTableName], $keyColumn);

                /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                foreach ($columnsList as $columnName => $attribute) {
                    $countTableName = 't' . $iterationNum++;
                    $joinCondition = sprintf(
                        'e.%3$s = %1$s.%3$s AND %1$s.attribute_id = %2$d AND %1$s.store_id = 0',
                        $countTableName,
                        $attribute->getId(),
                        $metadata->getLinkField()
                    );

                    $select->joinLeft(
                        [$countTableName => $tableName],
                        $joinCondition,
                        [$columnName => 'value']
                    );

                    if ($attribute->getFlatUpdateSelect($storeId) instanceof \Magento\Framework\DB\Select) {
                        $attributeCode = $attribute->getAttributeCode();
                        $columnValueName = $attributeCode . $valueFieldSuffix;
                        if (isset($flatColumns[$columnValueName])) {
                            $valueJoinCondition = sprintf(
                                'e.%1$s = %2$s.option_id AND %2$s.store_id = 0',
                                $attributeCode,
                                $countTableName
                            );
                            $selectValue->joinLeft(
                                [
                                    $countTableName => $this->_productIndexerHelper->getTable(
                                        'eav_attribute_option_value'
                                    ),
                                ],
                                $valueJoinCondition,
                                [$columnValueName => $countTableName . '.value']
                            );
                            $valueColumns[] = $columnValueName;
                        }
                    }
                }

                if (!empty($changedIds)) {
                    $select->where($this->_connection->quoteInto('e.entity_id IN (?)', $changedIds));
                }

                $sql = $select->insertFromSelect($temporaryTableName, $columns, true);
                $this->_connection->query($sql);

                if (count($valueColumns) > 1) {
                    if (!empty($changedIds)) {
                        $selectValue->where($this->_connection->quoteInto('e.entity_id IN (?)', $changedIds));
                    }
                    $sql = $selectValue->insertFromSelect($temporaryValueTableName, $valueColumns, true);
                    $this->_connection->query($sql);
                }
            }
        }
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     * @deprecated 101.1.0
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
