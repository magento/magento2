<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch;
use Magento\Elasticsearch\Model\Adapter\ElasticsearchFactory;
use Magento\Store\Model\Store;
use Magento\CatalogSearch\Model\Indexer\Fulltext;

class IndexerHandler implements IndexerInterface
{
    /**
     * Default batch size
     */
    const DEFAULT_BATCH_SIZE = 500;

    /**
     * Scope identifier
     */
    const SCOPE_FIELD_NAME = 'scope';

    /**
     * @var Elasticsearch
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var Batch
     */
    protected $batch;

    /**
     * @var int
     */
    protected $batchSize;

    /**
     * @param ElasticsearchFactory $adapterFactory
     * @param Batch $batch
     * @param string $entityType
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        ElasticsearchFactory $adapterFactory,
        Batch $batch,
        array $data = [],
        $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->adapter = $adapterFactory->create();
        $this->data = $data;
        $this->batch = $batch;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $dimension = current($dimensions);
        $storeId = $dimension->getValue();
        $indexName = $this->getIndexMapping($this->data['indexer_id']);
        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $docs = $this->adapter->prepareDocsPerStore($documentsBatch, $storeId);
            $this->adapter->addDocs($docs, $storeId, $indexName);
        }
        $this->adapter->updateAlias($storeId, $indexName);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $dimension = current($dimensions);
        $storeId = $dimension->getValue();
        $indexName = $this->getIndexMapping($this->data['indexer_id']);
        $documentIds = [];
        foreach ($documents as $entityId => $document) {
            $documentIds[$entityId] = $entityId;
        }
        $this->adapter->deleteDocs($documentIds, $storeId, $indexName);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($dimensions)
    {
        $dimension = current($dimensions);
        $storeId = $dimension->getValue();
        $indexName = $this->getIndexMapping($this->data['indexer_id']);
        $this->adapter->cleanIndex($storeId, $indexName);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return $this->adapter->ping();
    }

    /**
     * Taking index name by indexer ID
     *
     * @param string $indexerId
     *
     * @return string
     */
    private function getIndexMapping($indexerId)
    {
        if ($indexerId == Fulltext::INDEXER_ID) {
            $indexName = 'product';
        } else {
            $indexName = $indexerId;
        }
        return $indexName;
    }
}
