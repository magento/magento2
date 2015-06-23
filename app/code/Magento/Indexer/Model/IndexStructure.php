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
     * @param Resource|Resource $resource
     * @param IndexScopeResolver $indexScopeResolver
     * @param FlatScopeResolver $flatScopeResolver
     */
    public function __construct(
        Resource $resource,
        IndexScopeResolver $indexScopeResolver,
        FlatScopeResolver $flatScopeResolver
    ) {
        $this->resource = $resource;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->flatScopeResolver = $flatScopeResolver;
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
        $table = $adapter->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false],
                'Entity ID'
            )->addColumn(
                'attribute_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false]
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
        $adapter->createTable($table);
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
            $name = $field['name'];
            $type = $field['type'];
            $size = $field['size'];
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
