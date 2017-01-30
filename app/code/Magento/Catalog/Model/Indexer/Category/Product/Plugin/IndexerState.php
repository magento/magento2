<?php
/**
 * Plugin for \Magento\Indexer\Model\Indexer\State model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

use Magento\Catalog\Model\Indexer\Category\Product;
use Magento\Catalog\Model\Indexer\Product\Category;
use Magento\Indexer\Model\Indexer\State;

class IndexerState
{
    /**
     * @var State
     */
    protected $state;

    /**
     * Related indexers IDs
     *
     * @var int[]
     */
    protected $indexerIds = [
        Category::INDEXER_ID,
        Product::INDEXER_ID,
    ];

    /**
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * Synchronize status for indexers
     *
     * @param State $state
     * @return State
     */
    public function afterSave(State $state)
    {
        if (in_array($state->getIndexerId(), $this->indexerIds)) {
            $indexerId = $state->getIndexerId() === Product::INDEXER_ID
                ? Category::INDEXER_ID
                : Product::INDEXER_ID;

            $relatedIndexerState = $this->state->loadByIndexer($indexerId);

            if ($relatedIndexerState->getStatus() !== $state->getStatus()) {
                $relatedIndexerState->setData('status', $state->getStatus());
                $relatedIndexerState->save();
            }
        }

        return $state;
    }
}
