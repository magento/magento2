<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;

abstract class AbstractPlugin
{
    /** @var \Magento\Indexer\Model\IndexerRegistry */
    protected $indexerRegistry;

    /**
     * @param \Magento\Indexer\Model\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\Indexer\Model\IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Reindex by product if indexer is not scheduled
     *
     * @param int $productId
     * @return void
     */
    protected function reindexRow($productId)
    {
        $indexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);
        if (!$indexer->isScheduled()) {
            $indexer->reindexRow($productId);
        }
    }

    /**
     * Reindex by product if indexer is not scheduled
     *
     * @param int[] $productIds
     * @return void
     */
    protected function reindexList(array $productIds)
    {
        $indexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);
        if (!$indexer->isScheduled()) {
            $indexer->reindexList($productIds);
        }
    }
}
