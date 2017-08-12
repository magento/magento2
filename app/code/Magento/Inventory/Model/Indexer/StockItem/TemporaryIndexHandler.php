<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer\StockItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface as SaveHandlerIndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Inventory\Model\Indexer\StockItemIndexerInterface;

/**
 * @todo add comment
 */
class TemporaryIndexHandler implements SaveHandlerIndexerInterface
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

        $this->switchIndex($dimensions);
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
        $this->resource->getConnection()->insertArray($this->getTempTableName($dimensions), $columns, $documents);
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->resource->getConnection()
                ->delete($this->getTempTableName($dimensions), ['stock_id in (?)' => $batchDocuments]);
        }
    }

    /**
     * @inheritdoc
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getTempTableName($dimensions), $dimensions);
        $this->indexStructure->create($this->getTempTableName($dimensions), [], $dimensions);
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

    /**
     * @param $dimensions
     */
    private function switchIndex($dimensions)
    {
        $tableName = $this->getTableName($dimensions);
        if ($this->resource->getConnection()->isTableExists($tableName)) {
            $this->resource->getConnection()->dropTable($tableName);
        }

        $temporalIndexTable = $this->getTempTableName($dimensions);
        $this->resource->getConnection()->renameTable($temporalIndexTable, $tableName);
    }

    /**
     * @param Dimension[] $dimensions
     * @return string
     */
    private function getTempTableName($dimensions)
    {
        $tableName = $this->getTableName($dimensions);
        $tableName .= \Magento\Framework\Indexer\Table\StrategyInterface::TMP_SUFFIX;

        return $tableName;
    }

    /**
     * @param Dimension[] $dimensions
     * @return string
     */
    private function getTableName($dimensions)
    {
        return $this->indexScopeResolver->resolve(StockItemIndexerInterface::INDEXER_ID, $dimensions);
    }
}
