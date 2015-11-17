<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch;
use Magento\Elasticsearch\Model\AdapterFactoryInterface;
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
     * @param AdapterFactoryInterface $adapterFactory
     * @param Batch $batch
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        AdapterFactoryInterface $adapterFactory,
        Batch $batch,
        array $data = [],
        $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->adapter = $adapterFactory->createAdapter();
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
        $storeId = $this->getStoreIdByDimension($dimension);
        $this->adapter->checkIndex();
        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $docs = $this->adapter->prepareDocsPerStore($documentsBatch, $storeId);
            $this->adapter->addDocs($docs);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $documentIds = [];
        foreach ($documents as $entityId => $document) {
            $documentIds []= $entityId;
        }
        $this->adapter->checkIndex();
        $this->adapter->deleteDocs($documentIds);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function cleanIndex($dimensions)
    {
        $this->adapter->checkIndex();
        $this->adapter->cleanIndex();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        return $this->adapter->ping();
    }

    /**
     * @param Dimension $dimension
     * @return int
     */
    private function getStoreIdByDimension($dimension)
    {
        return $dimension->getName() == self::SCOPE_FIELD_NAME ? $dimension->getValue() : Store::DEFAULT_STORE_ID;
    }
}
