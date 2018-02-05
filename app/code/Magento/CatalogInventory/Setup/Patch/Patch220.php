<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Setup\Patch;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Indexer\AbstractProcessor;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch220
{


    /**
     * @param StockConfigurationInterface $configuration
     */
    private $configuration;
    /**
     * @param StoreManagerInterface $storeManager
     */
    private $storeManager;
    /**
     * @param AbstractProcessor $indexerProcessor
     */
    private $indexerProcessor;

    /**
     * @param StockConfigurationInterface $configuration @param StoreManagerInterface $storeManager@param AbstractProcessor $indexerProcessor
     */
    public function __construct(StockConfigurationInterface $configuration,
                                StoreManagerInterface $storeManager,
                                AbstractProcessor $indexerProcessor)
    {
        $this->configuration = $configuration;
        $this->storeManager = $storeManager;
        $this->indexerProcessor = $indexerProcessor;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->upgradeCatalogInventoryStockItem($setup);

        $setup->endSetup();

    }

    private function upgradeCatalogInventoryStockItem($setup
    )
    {
        $setup->getConnection()->update(
            $setup->getTable('cataloginventory_stock_item'),
            ['website_id' => $this->configuration->getDefaultScopeId()],
            ['website_id = ?' => $this->storeManager->getWebsite()->getId()]
        );
        $this->indexerProcessor->getIndexer()->invalidate();

    }
}
