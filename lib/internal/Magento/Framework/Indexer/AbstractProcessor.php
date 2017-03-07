<?php
/**
 * @category    Magento
 * @package     Magento_Indexer
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer;

abstract class AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = '';

    /** @var \Magento\Framework\Indexer\IndexerRegistry */
    protected $indexerRegistry;

    /**
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Get indexer
     *
     * @return \Magento\Framework\Indexer\IndexerInterface
     */
    public function getIndexer()
    {
        return $this->indexerRegistry->get(static::INDEXER_ID);
    }

    /**
     * Run Row reindex
     *
     * @param int $id
     * @param bool $forceReindex
     * @return void
     */
    public function reindexRow($id, $forceReindex = false)
    {
        if (!$forceReindex && $this->isIndexerScheduled()) {
            return;
        }
        $this->getIndexer()->reindexRow($id);
    }

    /**
     * Run List reindex
     *
     * @param int[] $ids
     * @param bool $forceReindex
     * @return void
     */
    public function reindexList($ids, $forceReindex = false)
    {
        if (!$forceReindex && $this->isIndexerScheduled()) {
            return;
        }
        $this->getIndexer()->reindexList($ids);
    }

    /**
     * Run Full reindex
     *
     * @return void
     */
    public function reindexAll()
    {
        $this->getIndexer()->reindexAll();
    }

    /**
     * Mark Product price indexer as invalid
     *
     * @return void
     */
    public function markIndexerAsInvalid()
    {
        $this->getIndexer()->invalidate();
    }

    /**
     * Get processor indexer ID
     *
     * @return string
     */
    public function getIndexerId()
    {
        return static::INDEXER_ID;
    }

    /**
     * Check if indexer is on scheduled
     *
     * @return bool
     */
    public function isIndexerScheduled()
    {
        return $this->getIndexer()->isScheduled();
    }
}
