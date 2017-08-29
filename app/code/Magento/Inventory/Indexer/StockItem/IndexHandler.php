<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer\StockItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Inventory\Indexer\StockItemIndexerInterface;

/**
 * Index handler is responsible for index data manipulation
 */
class IndexHandler implements IndexerInterface
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
     * @var int
     */
    private $batchSize;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param IndexStructureInterface $indexStructure
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param Batch $batch
     * @param ResourceConnection $resourceConnection
     * @param int $batchSize
     */
    public function __construct(
        IndexStructureInterface $indexStructure,
        IndexScopeResolverInterface $indexScopeResolver,
        Batch $batch,
        ResourceConnection $resourceConnection,
        $batchSize
    ) {
        $this->indexStructure = $indexStructure;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->batch = $batch;
        $this->resourceConnection = $resourceConnection;
        $this->batchSize = $batchSize;
    }

    /**
     * @inheritdoc
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $columns = ['sku', 'quantity', 'status', 'stock_id'];
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->resourceConnection
                ->getConnection()
                ->insertArray($this->resolveTableName($dimensions), $columns, $batchDocuments);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->resourceConnection
                ->getConnection()
                ->delete($this->resolveTableName($dimensions), ['stock_id IN (?)' => $batchDocuments]);
        }
    }

    /**
     * @inheritdoc
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->resolveTableName($dimensions), $dimensions);
        $this->indexStructure->create($this->resolveTableName($dimensions), [], $dimensions);
    }

    /**
     * @param Dimension[] $dimensions
     * @return string
     */
    private function resolveTableName($dimensions)
    {
        $tableName = $this->indexScopeResolver->resolve(StockItemIndexerInterface::INDEXER_ID, $dimensions);
        return $tableName;
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
