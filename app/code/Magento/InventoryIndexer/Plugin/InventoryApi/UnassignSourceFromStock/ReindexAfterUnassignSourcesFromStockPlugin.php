<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Plugin\InventoryApi\UnassignSourceFromStock;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;
use Magento\InventoryApi\Api\UnassignSourceFromStockInterface;

/**
 * Invalidate StockIndexer
 */
class ReindexAfterUnassignSourcesFromStockPlugin
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
     * @param UnassignSourceFromStockInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(UnassignSourceFromStockInterface $subject)
    {
        $indexer = $this->indexerRegistry->get(StockIndexer::INDEXER_ID);
        if ($indexer->isValid()) {
            $indexer->invalidate();
        }
    }
}
