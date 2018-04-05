<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Plugin\InventoryApi;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\StockSourceLinksSaveInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

/**
 * Invalidate InventoryIndexer
 */
class InvalidateAfterStockSourceLinksSavePlugin
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
     * We don't need to neither process Stock Source Links save nor invalidate cache for Default Stock.
     *
     * @param StockSourceLinksSaveInterface $subject
     * @param void $result
     * @param StockSourceLinkInterface[] $links
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        StockSourceLinksSaveInterface $subject,
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
