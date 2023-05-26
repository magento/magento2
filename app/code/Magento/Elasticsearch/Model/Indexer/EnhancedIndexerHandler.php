<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor;
use Magento\Elasticsearch\Model\Adapter\Elasticsearch as ElasticsearchAdapter;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;

class EnhancedIndexerHandler extends IndexerHandler
{
    /**
     * @var ElasticsearchAdapter
     */
    private ElasticsearchAdapter $adapter;

    /**
     * @var array
     */
    private array $data = [];

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
     */
    public function __construct(
        IndexStructureInterface $indexStructure,
        ElasticsearchAdapter    $adapter,
        IndexNameResolver       $indexNameResolver,
        Batch                   $batch,
        ScopeResolverInterface  $scopeResolver,
        array                   $data = [],
        int                     $batchSize = self::DEFAULT_BATCH_SIZE,
        ?DeploymentConfig       $deploymentConfig = null,
        ?CacheContext           $cacheContext = null,
        ?Processor              $processor = null
    ) {
        $this->adapter = $adapter;

        parent::__construct(
            $indexStructure,
            $this->adapter,
            $indexNameResolver,
            $batch,
            $scopeResolver,
            $data,
            $batchSize,
            $deploymentConfig,
            $cacheContext,
            $processor
        );
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
}
