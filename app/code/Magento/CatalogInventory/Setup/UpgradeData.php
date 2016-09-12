<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Setup;

use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Processor
     */
    private $stockIndexerProcessor;

    /**
     * @param Configuration $configuration
     * @param Processor $stockIndexerProcessor
     */
    public function __construct(
        Configuration $configuration,
        Processor $stockIndexerProcessor
    ) {
        $this->configuration = $configuration;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.2') < 0)
        {
            $this->upgradeCatalogInventoryStockItem($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeCatalogInventoryStockItem($setup)
    {
        $setup->getConnection()->update(
            $setup->getTable('cataloginventory_stock_item'),
            ['website_id' => $this->configuration->getDefaultScopeId()]
        );
        $this->stockIndexerProcessor->getIndexer()->reindexAll();
    }
}
