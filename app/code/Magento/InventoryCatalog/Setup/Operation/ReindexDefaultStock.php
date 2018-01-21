<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Operation;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;

/**
 * CReindex default stock during installation
 */
class ReindexDefaultStock
{
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerFactory;

    /**
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param IndexerInterfaceFactory $indexerFactory
     */
    public function __construct(
        DefaultStockProviderInterface $defaultStockProvider,
        IndexerInterfaceFactory $indexerFactory
    ) {
        $this->defaultStockProvider = $defaultStockProvider;
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * Create default stock
     *
     * @return void
     */
    public function execute()
    {
        /** @var IndexerInterface $indexer */
        $indexer = $this->indexerFactory->create();
        $indexer->load(StockIndexer::INDEXER_ID);
        $indexer->reindexRow($this->defaultStockProvider->getId());
    }
}
