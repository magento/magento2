<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;

/**
 * Abstract plugin for indexers
 * @since 2.0.0
 */
abstract class AbstractPlugin
{
    /**
     * @var IndexerRegistry
     * @since 2.0.0
     */
    protected $indexerRegistry;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @since 2.0.0
     */
    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Reindex by product if indexer is not scheduled
     *
     * @param int $productId
     * @return void
     * @since 2.0.0
     */
    protected function reindexRow($productId)
    {
        $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);

        if (!$indexer->isScheduled()) {
            $indexer->reindexRow($productId);
        }
    }

    /**
     * Reindex by product if indexer is not scheduled
     *
     * @param int[] $productIds
     * @return void
     * @since 2.0.0
     */
    protected function reindexList(array $productIds)
    {
        $indexer = $this->indexerRegistry->get(FulltextIndexer::INDEXER_ID);

        if (!$indexer->isScheduled()) {
            $indexer->reindexList($productIds);
        }
    }
}
