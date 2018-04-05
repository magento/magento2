<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Plugin\InventoryApi;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
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
     * We don't need to neither process Stock Source Links delete nor invalidate cache for Default Stock.
     *
     * @param StockSourceLinksDeleteInterface $subject
     * @param void $result
     * @param StockSourceLinkInterface[] $links
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        StockSourceLinksDeleteInterface $subject,
        $result,
        array $links
    ) {
        foreach ($links as $link) {
            if ($this->defaultStockProvider->getId() !== $link->getStockId()) {
                $indexer = $this->indexerRegistry->get(InventoryIndexer::INDEXER_ID);
                if ($indexer->isValid()) {
                    $indexer->invalidate();
                }
                break;
            }
        }
    }
}
