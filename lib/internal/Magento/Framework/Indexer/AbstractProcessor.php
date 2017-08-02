<?php
/**
 * @category    Magento
 * @package     Magento_Indexer
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer;

/**
 * Class \Magento\Framework\Indexer\AbstractProcessor
 *
 * @since 2.0.0
 */
abstract class AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = '';

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     * @since 2.0.0
     */
    protected $indexerRegistry;

    /**
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function reindexAll()
    {
        $this->getIndexer()->reindexAll();
    }

    /**
     * Mark Product price indexer as invalid
     *
     * @return void
     * @since 2.0.0
     */
    public function markIndexerAsInvalid()
    {
        $this->getIndexer()->invalidate();
    }

    /**
     * Get processor indexer ID
     *
     * @return string
     * @since 2.0.0
     */
    public function getIndexerId()
    {
        return static::INDEXER_ID;
    }

    /**
     * Check if indexer is on scheduled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isIndexerScheduled()
    {
        return $this->getIndexer()->isScheduled();
    }
}
