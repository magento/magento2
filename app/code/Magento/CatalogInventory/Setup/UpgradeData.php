<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Setup;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Indexer\AbstractProcessor;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $configuration;

    /**
     * @var AbstractProcessor
     */
    private $indexerProcessor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StockConfigurationInterface $configuration
     * @param StoreManagerInterface $storeManager
     * @param AbstractProcessor $indexerProcessor
     */
    public function __construct(
        StockConfigurationInterface $configuration,
        StoreManagerInterface $storeManager,
        AbstractProcessor $indexerProcessor
    ) {
        $this->configuration = $configuration;
        $this->storeManager = $storeManager;
        $this->indexerProcessor = $indexerProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.2.0') < 0) {
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
            ['website_id' => $this->configuration->getDefaultScopeId()],
            ['website_id = ?' => $this->storeManager->getWebsite()->getId()]
        );
        $this->indexerProcessor->getIndexer()->invalidate();
    }
}
