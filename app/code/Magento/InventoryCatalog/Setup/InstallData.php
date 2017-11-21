<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\InventoryCatalog\Setup\Processor\AssignSourceToStockProcessor;
use Magento\InventoryCatalog\Setup\Processor\DefaultSourceProcessor;
use Magento\InventoryCatalog\Setup\Processor\DefaultStockProcessor;
use Magento\InventoryCatalog\Setup\Processor\StockItemProcessor;
use Magento\Inventory\Indexer\Stock\StockIndexer;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

/**
 * Install Default Source, Stock and link them together
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerFactory;

    /**
     * @var DefaultSourceProcessor
     */
    private $defaultSourceProcessor;

    /**
     * @var DefaultStockProcessor
     */
    private $defaultStockProcessor;

    /**
     * @var StockItemProcessor
     */
    private $stockItemProcessor;

    /**
     * @var AssignSourceToStockProcessor
     */
    private $assignSourceToStockProcessor;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param DefaultSourceProcessor $defaultSourceProcessor
     * @param DefaultStockProcessor $defaultStockProcessor
     * @param AssignSourceToStockProcessor $assignSourceToStockProcessor
     * @param StockItemProcessor $stockItemProcessor
     * @param IndexerInterfaceFactory $indexerFactory
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        DefaultSourceProcessor $defaultSourceProcessor,
        DefaultStockProcessor $defaultStockProcessor,
        AssignSourceToStockProcessor $assignSourceToStockProcessor,
        StockItemProcessor $stockItemProcessor,
        IndexerInterfaceFactory $indexerFactory,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->defaultSourceProcessor = $defaultSourceProcessor;
        $this->defaultStockProcessor = $defaultStockProcessor;
        $this->assignSourceToStockProcessor = $assignSourceToStockProcessor;
        $this->stockItemProcessor = $stockItemProcessor;
        $this->indexerFactory = $indexerFactory;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->defaultSourceProcessor->process();
        $this->defaultStockProcessor->process();
        $this->assignSourceToStockProcessor->process();
        $this->stockItemProcessor->process();
        $this->reindexDefaultStock();
    }

    /**
     * @return void
     */
    private function reindexDefaultStock()
    {
        /** @var IndexerInterface $indexer */
        $indexer = $this->indexerFactory->create();
        $indexer->load(StockIndexer::INDEXER_ID);
        $indexer->reindexRow($this->defaultStockProvider->getId());
    }
}
