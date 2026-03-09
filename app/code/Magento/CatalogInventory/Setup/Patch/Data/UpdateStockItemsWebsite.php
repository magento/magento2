<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Setup\Patch\Data;

use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpdateStockItemsWebsite patch
 */
class UpdateStockItemsWebsite implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Indexer\AbstractProcessor
     */
    private $indexerProcessor;

    /**
     * UpdateStockItemsWebsite constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Indexer\AbstractProcessor $indexerProcessor
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Processor $indexerProcessor
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->stockConfiguration = $stockConfiguration;
        $this->storeManager = $storeManager;
        $this->indexerProcessor = $indexerProcessor;
    }

    /**
     * Run code inside patch
     * If code fails, patch must be reverted, in case when we are speaking about schema - then under revert
     * means run PatchInterface::revert()
     *
     * If we speak about data, under revert means: $transaction->rollback()
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('cataloginventory_stock_item'),
            ['website_id' => $this->stockConfiguration->getDefaultScopeId()],
            ['website_id = ?' => $this->storeManager->getWebsite()->getId()]
        );
        $this->indexerProcessor->getIndexer()->invalidate();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            CreateDefaultStock::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.2.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
