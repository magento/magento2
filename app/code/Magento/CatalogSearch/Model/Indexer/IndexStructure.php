<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * @api
 * @since 2.0.0
 */
class IndexStructure implements IndexStructureInterface
{
    /**
     * @var Resource
     * @since 2.0.0
     */
    private $resource;

    /**
     * @var IndexScopeResolver
     * @since 2.0.0
     */
    private $indexScopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @since 2.0.0
     */
    public function __construct(
        ResourceConnection $resource,
        IndexScopeResolverInterface $indexScopeResolver
    ) {
        $this->resource = $resource;
        $this->indexScopeResolver = $indexScopeResolver;
    }

    /**
     * @param string $index
     * @param Dimension[] $dimensions
     * @return void
     * @since 2.0.0
     */
    public function delete($index, array $dimensions = [])
    {
        $tableName = $this->indexScopeResolver->resolve($index, $dimensions);
        if ($this->resource->getConnection()->isTableExists($tableName)) {
            $this->resource->getConnection()->dropTable($tableName);
        }
    }

    /**
     * @param string $index
     * @param array $fields
     * @param array $dimensions
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     * @since 2.0.0
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        $this->createFulltextIndex($this->indexScopeResolver->resolve($index, $dimensions));
    }

    /**
     * @param string $tableName
     * @throws \Zend_Db_Exception
     * @return void
     * @since 2.0.0
     */
    protected function createFulltextIndex($tableName)
    {
        $table = $this->resource->getConnection()->newTable($tableName)
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
        $this->resource->getConnection()->createTable($table);
    }
}
