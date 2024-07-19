<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Indexer\Plugin;

use Magento\Elasticsearch\Model\Config;
use Magento\Framework\Indexer\Config\DependencyInfoProvider as Provider;
use Magento\CatalogSearch\Model\Indexer\Fulltext as CatalogSearchFulltextIndexer;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as CatalogInventoryStockIndexer;

/**
 * Plugin for maintenance catalog search index dependency on stock index.
 * If elasticsearch is used as search engine catalog search index becomes dependent on stock index. Elasticsearch
 * module declares this dependence. But in case when elasticsearch module is enabled and elasticsearch engine isn`t
 * used as search engine other search engines don`t need this dependency.
 * This plugin remove catalog search index dependency on stock index when elasticsearch isn`t used as search engine
 * except full reindexing. During full reindexing this dependency doesn`t make overhead.
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
class DependencyUpdaterPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Remove index dependency, if it needed, on run reindexing by specifics indexes.
     *
     * @param Provider $provider
     * @param array $dependencies
     * @param string $indexerId
     * @return array
     * @see \Magento\Indexer\Console\Command\IndexerReindexCommand::getIndexers()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetIndexerIdsToRunBefore(Provider $provider, array $dependencies, string $indexerId): array
    {
        if ($this->isFilteringNeeded($indexerId, CatalogSearchFulltextIndexer::INDEXER_ID)) {
            $dependencies = array_diff($dependencies, [CatalogInventoryStockIndexer::INDEXER_ID]);
        }

        return $dependencies;
    }

    /**
     * Remove index dependency, if it needed, on reindex triggers.
     *
     * @param Provider $provider
     * @param array $dependencies
     * @param string $indexerId
     * @return array
     * @see \Magento\Indexer\Model\Indexer\DependencyDecorator
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetIndexerIdsToRunAfter(Provider $provider, array $dependencies, string $indexerId): array
    {
        if ($this->isFilteringNeeded($indexerId, CatalogInventoryStockIndexer::INDEXER_ID)) {
            $dependencies = array_diff($dependencies, [CatalogSearchFulltextIndexer::INDEXER_ID]);
        }

        return $dependencies;
    }

    /**
     * Check if filter needed
     *
     * @param string $currentIndexerId
     * @param string $targetIndexerId
     * @return bool
     */
    private function isFilteringNeeded(string $currentIndexerId, string $targetIndexerId): bool
    {
        return (!$this->config->isElasticsearchEnabled() && $targetIndexerId === $currentIndexerId);
    }
}
