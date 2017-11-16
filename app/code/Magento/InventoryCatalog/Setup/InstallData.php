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
use Magento\InventoryCatalog\Setup\Operation\AssignSourceToStock;
use Magento\InventoryCatalog\Setup\Operation\CreateDefaultSource;
use Magento\InventoryCatalog\Setup\Operation\CreateDefaultStock;

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
     * @var CreateDefaultSource
     */
    private $createDefaultSource;

    /**
     * @var CreateDefaultStock
     */
    private $createDefaultStock;

    /**
     * @var AssignSourceToStock
     */
    private $assignSourceToStock;

    /**
     * @param CreateDefaultSource $createDefaultSource
     * @param CreateDefaultStock $createDefaultStock
     * @param AssignSourceToStock $assignSourceToStock
     * @param StockItemIndexerInterface $stockItemIndexer
     */
    public function __construct(
        CreateDefaultSource $createDefaultSource,
        CreateDefaultStock $createDefaultStock,
        AssignSourceToStock $assignSourceToStock,
        StockItemIndexerInterface $stockItemIndexer
    ) {
        $this->createDefaultSource = $createDefaultSource;
        $this->createDefaultStock = $createDefaultStock;
        $this->assignSourceToStock = $assignSourceToStock;
        $this->stockItemIndexer = $stockItemIndexer;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->createDefaultSource->execute();
        $this->createDefaultStock->execute();
        $this->assignSourceToStock->execute();
        $this->stockItemIndexer->executeFull();
    }
}
