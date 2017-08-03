<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Framework\App\ScopeResolverInterface;

/**
 * Class \Magento\Elasticsearch\Model\Indexer\IndexerHandler
 *
 * @since 2.1.0
 */
class IndexerHandler implements IndexerInterface
{
    /**
     * Default batch size
     */
    const DEFAULT_BATCH_SIZE = 500;

    /**
     * @var IndexStructureInterface
     * @since 2.1.0
     */
    private $indexStructure;

    /**
     * @var ElasticsearchAdapter
     * @since 2.1.0
     */
    private $adapter;

    /**
     * @var IndexNameResolver
     * @since 2.1.0
     */
    private $indexNameResolver;

    /**
     * @var Batch
     * @since 2.1.0
     */
    private $batch;

    /**
     * @var array
     * @since 2.1.0
     */
    private $data;

    /**
     * @var int
     * @since 2.1.0
     */
    private $batchSize;

    /**
     * @var ScopeResolverInterface
     * @since 2.1.0
     */
    private $scopeResolver;

    /**
     * @param IndexStructureInterface $indexStructure
     * @param ElasticsearchAdapter $adapter
     * @param IndexNameResolver $indexNameResolver
     * @param Batch $batch
     * @param ScopeResolverInterface $scopeResolver
     * @param array $data
     * @param int $batchSize
     * @since 2.1.0
     */
    public function __construct(
        IndexStructureInterface $indexStructure,
        ElasticsearchAdapter $adapter,
        IndexNameResolver $indexNameResolver,
        Batch $batch,
        ScopeResolverInterface $scopeResolver,
        array $data = [],
        $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->indexStructure = $indexStructure;
        $this->adapter = $adapter;
        $this->indexNameResolver = $indexNameResolver;
        $this->batch = $batch;
        $this->data = $data;
        $this->batchSize = $batchSize;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $docs = $this->adapter->prepareDocsPerStore($documentsBatch, $scopeId);
            $this->adapter->addDocs($docs, $scopeId, $this->getIndexerId());
        }
        $this->adapter->updateAlias($scopeId, $this->getIndexerId());
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $documentIds = [];
        foreach ($documents as $document) {
            $documentIds[$document] = $document;
        }
        $this->adapter->deleteDocs($documentIds, $scopeId, $this->getIndexerId());
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexerId(), $dimensions);
        $this->indexStructure->create($this->getIndexerId(), [], $dimensions);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function isAvailable()
    {
        return $this->adapter->ping();
    }

    /**
     * @return string
     * @since 2.1.0
     */
    private function getIndexerId()
    {
        return $this->indexNameResolver->getIndexMapping($this->data['indexer_id']);
    }
}
