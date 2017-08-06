<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer\StockItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface as SaveHandlerIndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Inventory\Model\Indexer\StockItem;
use Magento\InventoryApi\Api\Data\StockItemManagerInterface;

/**
 * @todo add comment
 */
class IndexHandler implements SaveHandlerIndexerInterface
{
    /**#@+
     * Constants for keys of data array.
     * @var string
     */
    const DATA_KEY_INDEXER_ID = 'indexer_id';
    const DATA_KEY_INDEXER_SUFFIX = 'indexer_suffix';
    /**#@- */

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
     * @var array
     */
    private $data;

    /**
     * @param IndexStructure $indexStructure
     * @param IndexScopeResolverInterface $indexScopeResolver
     * @param Batch $batch
     * @param ResourceConnection $resource
     * @param array $data
     * @param int $batchSize #
     */
    public function __construct(
        IndexStructure $indexStructure,
        IndexScopeResolverInterface $indexScopeResolver,
        Batch $batch,
        ResourceConnection $resource,
        array $data,
        $batchSize = 100
    ) {
        $this->indexStructure = $indexStructure;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->batch = $batch;
        $this->resource = $resource;
        $this->data = $data;
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
     * @inheritdoc
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->resource->getConnection()
                ->delete($this->getTableName($dimensions), ['entity_id in (?)' => $batchDocuments]);
        }
    }

    /**
     * @inheritdoc
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexName(), $dimensions);
        $this->indexStructure->create($this->getIndexName(), [], $dimensions);
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
     * Insert the given field in the table
     *
     * @param array $documents
     * @param array $dimensions
     * @return void
     */
    private function insertDocuments(array $documents, array $dimensions)
    {
        // TODO: Implement insertDocuments() method.
    }

    /**
     * @param Dimension[] $dimensions
     * @return string
     */
    private function getTableName($dimensions)
    {
        $suffix = $this->getIndexSuffix();
        return $suffix . $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);
    }

    /**
     * @return string
     */
    private function getIndexName()
    {
        return $this->data[self::DATA_KEY_INDEXER_ID];
    }

    /**
     * Return the table suffix.
     *
     * @return string
     */
    private function getIndexSuffix()
    {
        if (isset($this->data[self::DATA_KEY_INDEXER_SUFFIX])) {
            return $this->data[self::DATA_KEY_INDEXER_SUFFIX];
        }
        return '';
    }
}
