<?php
/**
 * @category    Magento
 * @package     Magento_Indexer
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Indexer\Model\Indexer;

abstract class AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = '';

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
     * Get indexer
     *
     * @return \Magento\Indexer\Model\IndexerInterface
     */
    public function getIndexer()
    {
        return $this->indexerRegistry->get(static::INDEXER_ID);
    }

    /**
     * Run Row reindex
     *
     * @param int $id
     * @return void
     */
    public function reindexRow($id)
    {
        if ($this->getIndexer()->isScheduled()) {
            return;
        }
        $this->getIndexer()->reindexRow($id);
    }

    /**
     * Run List reindex
     *
     * @param int[] $ids
     * @return void
     */
    public function reindexList($ids)
    {
        if ($this->getIndexer()->isScheduled()) {
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
}
