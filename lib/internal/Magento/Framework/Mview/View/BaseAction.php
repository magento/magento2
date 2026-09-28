<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Action the use indexer to reindex items
 */
class BaseAction implements \Magento\Framework\Mview\ActionInterface
{
    /**
     * @param IndexerRegistry $indexerRegistry
     * @param string $indexerId
     */
    public function __construct(
        private IndexerRegistry $indexerRegistry,
        private string $indexerId
    ) {
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        /** @var  \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $this->indexerRegistry->get($this->indexerId);
        $indexer->reindexList($ids);
    }
}
