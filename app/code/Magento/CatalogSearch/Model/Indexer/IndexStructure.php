<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * Catalog search index structure.
 *
 * @api
 * @since 100.0.2
 */
class IndexStructure implements IndexStructureInterface
{
    /**
     * @var IndexStructureInterface
     */
    private $indexStructureEntity;

    /**
     * @var IndexStructureFactory
     */
    private $indexStructureFactory;

    /**
     * @var Resource
     * @deprecated
     * @see \Magento\Elasticsearch
     */
    private $resource;

    /**
     * @var IndexScopeResolver
     * @deprecated
     * @see \Magento\Elasticsearch
     */
    private $indexScopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param IndexStructureFactory|null $indexStructureFactory
     */
    public function __construct(
        ResourceConnection $resource,
        IndexScopeResolverInterface $indexScopeResolver,
        IndexStructureFactory $indexStructureFactory = null
    ) {
        $this->resource = $resource;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->indexStructureFactory = $indexStructureFactory ? : ObjectManager::getInstance()
            ->get(IndexStructureFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function delete($index, array $dimensions = [])
    {
        return $this->getEntity()->delete($index, $dimensions);
    }

    /**
     * @inheritdoc
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        return $this->getEntity()->create($index, $fields, $dimensions);
    }

    /**
     * Get instance of current index structure
     *
     * @return IndexStructureInterface
     */
    private function getEntity()
    {
        if (empty($this->indexStructureEntity)) {
            $this->indexStructureEntity = $this->indexStructureFactory->create();
        }
        return $this->indexStructureEntity;
    }

    /**
     * Create fulltext index table.
     *
     * @param string $tableName
     * @throws \Zend_Db_Exception
     * @return void
     * @deprecated
     * @see \Magento\ElasticSearch
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
