<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;

class IndexerHandler implements IndexerInterface
{
    /**
     * Default batch size
     */
    const DEFAULT_BATCH_SIZE = 500;

    /**
     * @var IndexStructureInterface
     */
    private $indexStructure;

    /**
     * @var ElasticsearchAdapter
     */
    private $adapter;

    /**
     * @var IndexNameResolver
     */
    private $indexNameResolver;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param IndexStructureInterface $indexStructure
     * @param ElasticsearchAdapter $adapter
     * @param IndexNameResolver $indexNameResolver
     * @param Batch $batch
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        IndexStructureInterface $indexStructure,
        ElasticsearchAdapter $adapter,
        IndexNameResolver $indexNameResolver,
        Batch $batch,
        array $data = [],
        $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->indexStructure = $indexStructure;
        $this->adapter = $adapter;
        $this->indexNameResolver = $indexNameResolver;
        $this->batch = $batch;
        $this->data = $data;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $dimension = current($dimensions);
        $storeId = $dimension->getValue();
        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $docs = $this->adapter->prepareDocsPerStore($documentsBatch, $storeId);
            $this->adapter->addDocs($docs, $storeId, $this->getIndexerId());
        }
        $this->adapter->updateAlias($storeId, $this->getIndexerId());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $dimension = current($dimensions);
        $storeId = $dimension->getValue();
        $documentIds = [];
        foreach ($documents as $document) {
            $documentIds[$document] = $document;
        }
        $this->adapter->deleteDocs($documentIds, $storeId, $this->getIndexerId());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexerId(), $dimensions);
        $this->indexStructure->create($this->getIndexerId(), [], $dimensions);
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
     * @return string
     */
    private function getIndexerId()
    {
        return $this->indexNameResolver->getIndexMapping($this->data['indexer_id']);
    }
}
