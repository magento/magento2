<?php
/**
 * Plugin for \Magento\Indexer\Model\Indexer\State model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

class IndexerState
{
    /**
     * @var \Magento\Indexer\Model\Indexer\State
     */
    protected $state;

    /**
     * Related indexers IDs
     *
     * @var int[]
     */
    protected $indexerIds = [
        \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID,
        \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID,
    ];

    /**
     * @param \Magento\Indexer\Model\Indexer\State $state
     */
    public function __construct(\Magento\Indexer\Model\Indexer\State $state)
    {
        $this->state = $state;
    }

    /**
     * Synchronize status for indexers
     *
     * @param \Magento\Indexer\Model\Indexer\State $state
     * @return \Magento\Indexer\Model\Indexer\State
     */
    public function afterSetStatus(\Magento\Indexer\Model\Indexer\State $state)
    {
        if (in_array($state->getIndexerId(), $this->indexerIds)) {
            $indexerId = $state->getIndexerId() ==
                \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID ? \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID : \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;

            $relatedIndexerState = $this->state->loadByIndexer($indexerId);

            $relatedIndexerState->setData('status', $state->getStatus());
            $relatedIndexerState->save();
        }

        return $state;
    }
}
