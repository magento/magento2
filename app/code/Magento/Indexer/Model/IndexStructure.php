<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model;


use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Request\Dimension;
use Magento\Search\Model\ScopeResolver\FlatScopeResolver;
use Magento\Search\Model\ScopeResolver\IndexScopeResolver;

class IndexStructure
{
    /**
     * @var Resource
     */
    private $resource;
    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;
    /**
     * @var FlatScopeResolver
     */
    private $flatScopeResolver;

    /**
     * @var array
     */
    protected $columnTypesMap = [
        'varchar'    => ['type' => Table::TYPE_TEXT, 'size' => 255],
        'mediumtext' => ['type' => Table::TYPE_TEXT, 'size' => 16777216],
        'text'       => ['type' => Table::TYPE_TEXT, 'size' => 65536],
    ];

    /**
     * @param Resource|Resource $resource
     * @param IndexScopeResolver $indexScopeResolver
     * @param FlatScopeResolver $flatScopeResolver
     * @param array $columnTypesMap
     */
    public function __construct(
        Resource $resource,
        IndexScopeResolver $indexScopeResolver,
        FlatScopeResolver $flatScopeResolver,
        array $columnTypesMap = []
    ) {
        $this->resource = $resource;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->flatScopeResolver = $flatScopeResolver;
        $this->columnTypesMap = array_merge($this->columnTypesMap, $columnTypesMap);
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     */
    public function delete($index, array $dimensions)
    {
        $adapter = $this->getAdapter();
        $this->dropTable($adapter, $this->indexScopeResolver->resolve($index, $dimensions));
        $this->dropTable($adapter, $this->flatScopeResolver->resolve($index, $dimensions));
    }

    /**
     * @param string $table
     * @param array $filterFields
     * @param Dimension[] $dimensions
     */
    public function create($table, array $filterFields, array $dimensions)
    {
        $this->createFulltextIndex($this->indexScopeResolver->resolve($table, $dimensions));
        if ($filterFields) {
            $this->createFlatIndex($this->flatScopeResolver->resolve($table, $dimensions), $filterFields);
        }
    }

    /**
     * @param string $tableName
     * @throws \Zend_Db_Exception
     */
    protected function createFulltextIndex($tableName)
    {
        $adapter = $this->getAdapter();
        $table = $this->configureFulltextTable($adapter->newTable($tableName));
        $adapter->createTable($table);
    }

    /**
     * @param Table $table
     * @return mixed
     */
    protected function configureFulltextTable(Table $table)
    {
        $table->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false],
                'Entity ID'
            )->addColumn(
                'attribute_id',
                Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => true]
            )->addColumn(
                'data_index',
                Table::TYPE_TEXT,
                '4g',
                ['nullable' => true],
                'Data index'
            )->addIndex(
                'idx_primary',
                ['entity_id', 'attribute_id'],
                ['type' => AdapterInterface::INDEX_TYPE_PRIMARY]
            )->addIndex(
                'FTI_FULLTEXT_DATA_INDEX',
                ['data_index'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            );
        return $table;
    }

    /**
     * @param string $tableName
     * @param array $fields
     * @throws \Zend_Db_Exception
     */
    protected function createFlatIndex($tableName, array $fields)
    {
        $adapter = $this->getAdapter();
        $table = $adapter->newTable($tableName);
        foreach ($fields as $field) {
            $columnMap = isset($this->columnTypesMap[$field['dataType']])
                ? $this->columnTypesMap[$field['dataType']]
                : ['type' => $field['type'], 'size' => 10];
            $name = $field['name'];
            $type = $columnMap['type'];
            $size = $columnMap['size'];
            $table->addColumn($name, $type, $size);
        }
        $adapter->createTable($table);
    }

    /**
     * @return false|AdapterInterface
     */
    private function getAdapter()
    {
        $adapter = $this->resource->getConnection('write');
        return $adapter;
    }

    /**
     * @param AdapterInterface $adapter
     * @param string $tableName
     */
    private function dropTable(AdapterInterface $adapter, $tableName)
    {
        if ($adapter->isTableExists($tableName)) {
            $adapter->dropTable($tableName);
        }
    }
}
