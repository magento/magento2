<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Catalog\Model\Category;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\StackedActionsIndexerInterface;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Indexer\CacheContext;

/**
 * Indexer Handler for Elasticsearch engine.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerHandler implements IndexerInterface, StackedActionsIndexerInterface
{
    /**
     * Size of default batch
     */
    public const DEFAULT_BATCH_SIZE = 500;

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
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var DeploymentConfig|null
     */
    private $deploymentConfig;

    /**
     * Deployment config path
     *
     * @var string
     */
    private const DEPLOYMENT_CONFIG_INDEXER_BATCHES = 'indexer/batch_size/';

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * IndexerHandler constructor.
     * @param IndexStructureInterface $indexStructure
     * @param ElasticsearchAdapter $adapter
     * @param IndexNameResolver $indexNameResolver
     * @param Batch $batch
     * @param ScopeResolverInterface $scopeResolver
     * @param array $data
     * @param int $batchSize
     * @param DeploymentConfig|null $deploymentConfig
     * @param CacheContext|null $cacheContext
     * @param Processor|null $processor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        IndexStructureInterface $indexStructure,
        ElasticsearchAdapter $adapter,
        IndexNameResolver $indexNameResolver,
        Batch $batch,
        ScopeResolverInterface $scopeResolver,
        array $data = [],
        int $batchSize = self::DEFAULT_BATCH_SIZE,
        ?DeploymentConfig $deploymentConfig = null,
        ?CacheContext $cacheContext = null,
        ?Processor $processor = null
    ) {
        $this->indexStructure = $indexStructure;
        $this->adapter = $adapter;
        $this->indexNameResolver = $indexNameResolver;
        $this->batch = $batch;
        $this->data = $data;
        $this->batchSize = $batchSize;
        $this->scopeResolver = $scopeResolver;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
        $this->cacheContext = $cacheContext ?: ObjectManager::getInstance()->get(CacheContext::class);
        $this->processor = $processor ?: ObjectManager::getInstance()->get(Processor::class);
    }

    /**
     * Disables stacked actions mode
     *
     * @return void
     */
    public function disableStackedActions(): void
    {
        $this->adapter->disableStackQueriesMode();
    }

    /**
     * Enables stacked actions mode
     *
     * @return void
     */
    public function enableStackedActions(): void
    {
        $this->adapter->enableStackQueriesMode();
    }

    /**
     * Runs stacked actions
     *
     * @return void
     * @throws \Exception
     */
    public function triggerStackedActions(): void
    {
        $this->adapter->triggerStackedQueries();
    }

    /**
     * @inheritdoc
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();

        $this->batchSize = $this->deploymentConfig->get(
            self::DEPLOYMENT_CONFIG_INDEXER_BATCHES . Fulltext::INDEXER_ID . '/elastic_save'
        ) ?? $this->batchSize;

        foreach ($this->batch->getItems($documents, $this->batchSize) as $documentsBatch) {
            $docs = $this->adapter->prepareDocsPerStore($documentsBatch, $scopeId);
            $this->adapter->addDocs($docs, $scopeId, $this->getIndexerId());
            if ($this->processor->getIndexer()->isScheduled()) {
                $this->updateCacheContext($docs);
            }
        }
        $this->adapter->updateAlias($scopeId, $this->getIndexerId());
        return $this;
    }

    /**
     * Add category cache tags for the affected products to the cache context
     *
     * @param array $docs
     * @return void
     */
    private function updateCacheContext(array $docs) : void
    {
        $categoryIds = [];
        foreach ($docs as $document) {
            if (!empty($document['category_ids'])) {
                if (is_array($document['category_ids'])) {
                    foreach ($document['category_ids'] as $id) {
                        $categoryIds[] = $id;
                    }
                } elseif (is_numeric($document['category_ids'])) {
                    $categoryIds[] = $document['category_ids'];
                }
            }
        }
        if (!empty($categoryIds)) {
            $categoryIds = array_unique($categoryIds);
            $this->cacheContext->registerEntities(Category::CACHE_TAG, $categoryIds);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $dimension = current($dimensions);
        $scopeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $documentIds = [];
        foreach ($documents as $document) {
            if ($document) {
                $documentIds[$document] = $document;
            }
        }
        $this->adapter->deleteDocs($documentIds, $scopeId, $this->getIndexerId());
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexerId(), $dimensions);
        $this->indexStructure->create($this->getIndexerId(), [], $dimensions);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable($dimensions = [])
    {
        return $this->adapter->ping();
    }

    /**
     * Update mapping data for index.
     *
     * @param Dimension[] $dimensions
     * @param string $attributeCode
     * @return IndexerInterface
     */
    public function updateIndex(array $dimensions, string $attributeCode): IndexerInterface
    {
        $dimension = current($dimensions);
        $scopeId = (int)$this->scopeResolver->getScope($dimension->getValue())->getId();
        $this->adapter->updateIndexMapping($scopeId, $this->getIndexerId(), $attributeCode);

        return $this;
    }

    /**
     * Returns indexer id.
     *
     * @return string
     */
    private function getIndexerId()
    {
        return $this->indexNameResolver->getIndexMapping($this->data['indexer_id']);
    }
}
