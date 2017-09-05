<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Product;

use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\PageCache\Model\Config;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Reindex all relevant product indexers
 */
class Full implements ActionInterface
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var Config
     */
    private $pageCacheConfig;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var string[]
     */
    private $indexerList;

    /**
     * Initialize dependencies
     *
     * @param IndexerRegistry $indexerRegistry
     * @param Config $pageCacheConfig
     * @param TypeListInterface $cacheTypeList
     * @param string[] $indexerList
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        Config $pageCacheConfig,
        TypeListInterface $cacheTypeList,
        array $indexerList
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->pageCacheConfig = $pageCacheConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->indexerList = $indexerList;
    }

    /**
     * {@inheritdoc}
     */
    public function executeFull()
    {
        foreach ($this->indexerList as $indexerName) {
            $indexer = $this->indexerRegistry->get($indexerName);
            if (!$indexer->isScheduled()) {
                $indexer->reindexAll();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function executeList(array $ids)
    {
        if (!empty($ids)) {
            foreach ($this->indexerList as $indexerName) {
                $indexer = $this->indexerRegistry->get($indexerName);
                if (!$indexer->isScheduled()) {
                    $indexer->reindexList($ids);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function executeRow($id)
    {
        if (!empty($id)) {
            foreach ($this->indexerList as $indexerName) {
                $indexer = $this->indexerRegistry->get($indexerName);
                if (!$indexer->isScheduled()) {
                    $indexer->reindexRow($id);
                }
            }
        }
    }
}
