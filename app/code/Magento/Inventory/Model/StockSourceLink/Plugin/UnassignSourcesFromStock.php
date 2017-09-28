<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\StockSourceLink\Plugin;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Inventory\Indexer\StockItemIndexerInterface;

/**
 * @inheritdoc
 */
class UnassignSourcesFromStock
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @inheritdoc
     */
    public function afterExecute()
    {
        $indexer = $this->indexerRegistry->get(StockItemIndexerInterface::INDEXER_ID);
        if ($indexer->isValid()) {
            $indexer->invalidate();
        }
    }
}
