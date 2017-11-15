<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Inventory\Indexer\StockItemIndexerInterface;
use Magento\InventoryCatalog\Setup\Processor\AssignSourceToStockProcessor;
use Magento\InventoryCatalog\Setup\Processor\DefaultSourceProcessor;
use Magento\InventoryCatalog\Setup\Processor\DefaultStockProcessor;
use Magento\InventoryCatalog\Setup\Processor\StockItemProcessor;

/**
 * Install Default Source, Stock and link them together
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var StockItemIndexerInterface
     */
    private $stockItemIndexer;

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
     * @param DefaultSourceProcessor $defaultSourceProcessor
     * @param DefaultStockProcessor $defaultStockProcessor
     * @param AssignSourceToStockProcessor $assignSourceToStockProcessor
     * @param StockItemProcessor $stockItemProcessor ,
     * @param StockItemIndexerInterface $stockItemIndexer
     */
    public function __construct(
        DefaultSourceProcessor $defaultSourceProcessor,
        DefaultStockProcessor $defaultStockProcessor,
        AssignSourceToStockProcessor $assignSourceToStockProcessor,
        StockItemProcessor $stockItemProcessor,
        StockItemIndexerInterface $stockItemIndexer
    ) {
        $this->defaultSourceProcessor = $defaultSourceProcessor;
        $this->defaultStockProcessor = $defaultStockProcessor;
        $this->assignSourceToStockProcessor = $assignSourceToStockProcessor;
        $this->stockItemProcessor = $stockItemProcessor;
        $this->stockItemIndexer = $stockItemIndexer;
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
        $this->stockItemIndexer->executeFull();
    }
}
