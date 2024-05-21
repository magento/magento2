<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Product;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;

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
     * @var string[]
     */
    private $indexerList;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Initialize dependencies
     *
     * @param IndexerRegistry $indexerRegistry
     * @param string[] $indexerList
     * @param ConfigInterface|null $config
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        array $indexerList,
        ?ConfigInterface $config = null
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->indexerList = $indexerList;
        $this->config = $config
            ?? ObjectManager::getInstance()->get(ConfigInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        foreach ($this->getIndexerList() as $indexerName) {
            $indexer = $this->indexerRegistry->get($indexerName);
            if (!$indexer->isScheduled()) {
                $indexer->reindexAll();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $ids)
    {
        if (!empty($ids)) {
            foreach ($this->getIndexerList() as $indexerName) {
                $indexer = $this->indexerRegistry->get($indexerName);
                if (!$indexer->isScheduled()) {
                    $indexer->reindexList($ids);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function executeRow($id)
    {
        if (!empty($id)) {
            foreach ($this->getIndexerList() as $indexerName) {
                $indexer = $this->indexerRegistry->get($indexerName);
                if (!$indexer->isScheduled()) {
                    $indexer->reindexRow($id);
                }
            }
        }
    }

    /**
     * Returns indexers in the order according to dependency tree
     *
     * @return array
     */
    private function getIndexerList(): array
    {
        $indexers = [];
        foreach (array_keys($this->config->getIndexers()) as $indexerId) {
            if (in_array($indexerId, $this->indexerList, true)) {
                $indexers[] = $indexerId;
            }
        }

        return $indexers;
    }
}
