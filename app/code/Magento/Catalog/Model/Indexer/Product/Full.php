<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Product;

use Magento\Framework\Indexer\ActionInterface;
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
     * Initialize dependencies
     *
     * @param IndexerRegistry $indexerRegistry
     * @param string[] $indexerList
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        array $indexerList
    ) {
        $this->indexerRegistry = $indexerRegistry;
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
