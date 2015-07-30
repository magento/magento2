<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Request\Dimension;
use Magento\Indexer\Model\ScopeResolver\IndexScopeResolver;

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
     * @param Resource $resource
     * @param IndexScopeResolver $indexScopeResolver
     */
    public function __construct(
        Resource $resource,
        IndexScopeResolver $indexScopeResolver
    ) {
        $this->resource = $resource;
        $this->indexScopeResolver = $indexScopeResolver;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     */
    public function delete($index, array $dimensions)
    {
        $adapter = $this->getAdapter();
        $tableName = $this->indexScopeResolver->resolve($index, $dimensions);
        if ($adapter->isTableExists($tableName)) {
            $adapter->dropTable($tableName);
        }
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     */
    public function create($index, array $dimensions)
    {
        $this->createFulltextIndex($this->indexScopeResolver->resolve($index, $dimensions));
    }

    /**
     * @param string $tableName
     * @throws \Zend_Db_Exception
     * @return void
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
     * @return false|AdapterInterface
     */
    private function getAdapter()
    {
        $adapter = $this->resource->getConnection(Resource::DEFAULT_WRITE_RESOURCE);
        return $adapter;
    }
}
