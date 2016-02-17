<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer\Plugin;

use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\Indexer\Model\Indexer\State;

/**
 * Class IndexerState
 * @package Magento\Catalog\Model\Indexer\Category\Product\Plugin
 */
class IndexerState
{
    /**
     * @var State
     */
    protected $state;

    /**
     * Related indexers IDs
     *
     * @var string[]
     */
    protected $indexerIds = [
        ProductRuleProcessor::INDEXER_ID,
        RuleProductProcessor::INDEXER_ID,
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
            $indexerIdForValidation = $state->getIndexerId() === ProductRuleProcessor::INDEXER_ID
                ? RuleProductProcessor::INDEXER_ID
                : ProductRuleProcessor::INDEXER_ID;

            $relatedIndexerState = $this->state->loadByIndexer($indexerIdForValidation);

            if ($relatedIndexerState->getStatus() !== $state->getStatus()) {
                $relatedIndexerState->setData('status', $state->getStatus());
                $relatedIndexerState->save();
            }
        }

        return $state;
    }
}
