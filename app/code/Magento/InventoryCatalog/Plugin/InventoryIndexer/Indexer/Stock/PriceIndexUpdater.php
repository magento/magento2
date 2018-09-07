<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryIndexer\Indexer\Stock;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;

/**
 * Invalidate price indexer after stock index.
 */
class PriceIndexUpdater
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @param StockIndexer $subject
     * @param $result
     * @param array $stockIds
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(
        StockIndexer $subject,
        $result,
        array $stockIds
    ): void {
        $indexer = $this->indexerRegistry->get(Processor::INDEXER_ID);
        if ($indexer->isValid()) {
            $indexer->invalidate();
        }
    }
}
