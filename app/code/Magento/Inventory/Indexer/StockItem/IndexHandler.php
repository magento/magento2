<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface as SaveHandlerIndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Inventory\Indexer\StockItemIndexerInterface;

/**
 * @todo add comment
 */
class IndexHandler implements SaveHandlerIndexerInterface
{

    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @var DimensionFactory
     */
    private $indexScopeResolver;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var
     */
    private $batchSize;

    /**
     * @var  ResourceConnection
     */
    private $resource;

    /**
     * IndexHandler constructor.
     * @param IndexStructureInterface $indexStructure
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param Batch $batch
     * @param ResourceConnection $resource
     * @param string $indexName
     * @param int $batchSize
     */
    public function __construct(
        IndexStructureInterface $indexStructure,
        IndexScopeResolverInterface $indexScopeResolver,
        Batch $batch,
        ResourceConnection $resource,
        $batchSize = 100
    ) {
        $this->indexStructure = $indexStructure;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->batch = $batch;
        $this->resource = $resource;
        $this->batchSize = $batchSize;
    }

    /**
     * @inheritdoc
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->insertDocuments($batchDocuments, $dimensions);
        }
    }

    /**
     * Insert the given field in the table
     *
     * @param array $documents
     * @param array $dimensions
     * @return void
     */
    private function insertDocuments(array $documents, array $dimensions)
    {
        $columns = ['sku', 'quantity', 'status', 'stock_id'];
        $this->resource->getConnection()->insertArray($this->getTableName($dimensions), $columns, $documents);
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->resource->getConnection()
                ->delete($this->getTableName($dimensions), ['stock_id in (?)' => $batchDocuments]);
        }
    }

    /**
     * @param Dimension[] $dimensions
     * @return string
     */
    private function getTableName($dimensions)
    {
        $tableName =  $this->indexScopeResolver->resolve(StockItemIndexerInterface::INDEXER_ID, $dimensions);
        return $tableName;
    }

    /**
     * @inheritdoc
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getTableName($dimensions), $dimensions);
        $this->indexStructure->create($this->getTableName($dimensions), [], $dimensions);
    }

    /**
     * Define if engine is available
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }
}
