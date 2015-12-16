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
     * @var \Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver
     */
    protected $indexNameResolver;

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
     * @param \Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver
     * @param Batch $batch
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        ElasticsearchFactory $adapterFactory,
        \Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver $indexNameResolver,
        Batch $batch,
        array $data = [],
        $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->adapter = $adapterFactory->create();
        $this->indexNameResolver = $indexNameResolver;
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
        $indexName = $this->indexNameResolver->getIndexMapping($this->data['indexer_id']);
        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $docs = $this->adapter->prepareDocsPerStore($documentsBatch, $storeId);
            $this->adapter->addDocs($docs, $storeId, $indexName);
        }
        $this->adapter->updateAlias($storeId, $indexName);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $dimension = current($dimensions);
        $storeId = $dimension->getValue();
        $indexName = $this->indexNameResolver->getIndexMapping($this->data['indexer_id']);
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
        $indexName = $this->indexNameResolver->getIndexMapping($this->data['indexer_id']);
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
}
