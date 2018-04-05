<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Plugin\InventoryApi;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\InventoryApi\Api\StockSourceLinksDeleteInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

/**
 * Invalidate InventoryIndexer
 */
class InvalidateAfterStockSourceLinksDeletePlugin
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Invalidate index after source links have been deleted.
     *
     * @param StockSourceLinksDeleteInterface $subject
     * @param void $result
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        StockSourceLinksDeleteInterface $subject,
        $result
    ) {
        $indexer = $this->indexerRegistry->get(InventoryIndexer::INDEXER_ID);
        if ($indexer->isValid()) {
            $indexer->invalidate();
        }
    }
}
