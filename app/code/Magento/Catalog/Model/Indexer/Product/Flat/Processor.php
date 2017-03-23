<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

class Processor extends \Magento\Framework\Indexer\AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = 'catalog_product_flat';

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    protected $_state;

    /**
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param State $state
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $state
    ) {
        parent::__construct($indexerRegistry);
        $this->_state = $state;
    }

    /**
     * Reindex single row by id
     *
     * @param int $id
     * @param bool $forceReindex
     * @return void
     */
    public function reindexRow($id, $forceReindex = false)
    {
        if (!$this->_state->isFlatEnabled() || (!$forceReindex && $this->getIndexer()->isScheduled())) {
            return;
        }
        $this->getIndexer()->reindexRow($id);
    }

    /**
     * Reindex multiple rows by ids
     *
     * @param int[] $ids
     * @param bool $forceReindex
     * @return void
     */
    public function reindexList($ids, $forceReindex = false)
    {
        if (!$this->_state->isFlatEnabled() || (!$forceReindex && $this->getIndexer()->isScheduled())) {
            return;
        }
        $this->getIndexer()->reindexList($ids);
    }

    /**
     * Run full reindex
     *
     * @return void
     */
    public function reindexAll()
    {
        if (!$this->_state->isFlatEnabled()) {
            return;
        }
        $this->getIndexer()->reindexAll();
    }

    /**
     * Mark Product flat indexer as invalid
     *
     * @return void
     */
    public function markIndexerAsInvalid()
    {
        if (!$this->_state->isFlatEnabled()) {
            return;
        }
        $this->getIndexer()->invalidate();
    }
}
