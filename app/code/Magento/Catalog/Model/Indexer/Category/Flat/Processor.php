<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Category\Flat;

use Magento\Framework\Indexer\AbstractProcessor;
use Magento\Framework\Indexer\IndexerRegistry;

class Processor extends AbstractProcessor
{
    public const INDEXER_ID = State::INDEXER_ID;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param State $state
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        private readonly State $state
    ) {
        parent::__construct($indexerRegistry);
    }

    /**
     * @inheritdoc
     */
    public function reindexRow($id, $forceReindex = false)
    {
        if (!$this->state->isFlatEnabled()) {
            return;
        }

        parent::reindexRow($id, $forceReindex);
    }

    /**
     * @inheritdoc
     */
    public function reindexList($ids, $forceReindex = false)
    {
        if (!$this->state->isFlatEnabled()) {
            return;
        }

        parent::reindexList($ids, $forceReindex);
    }

    /**
     * @inheritdoc
     */
    public function reindexAll()
    {
        if (!$this->state->isFlatEnabled()) {
            return;
        }

        parent::reindexAll();
    }

    /**
     * @inheritdoc
     */
    public function markIndexerAsInvalid()
    {
        if (!$this->state->isFlatEnabled()) {
            return;
        }

        parent::markIndexerAsInvalid();
    }
}
