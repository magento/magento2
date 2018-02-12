<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Setup\Patch\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class UpdateStockItemsWebsite
 * @package Magento\CatalogInventory\Setup\Patch
 */
class UpdateStockItemsWebsite implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

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
     * @param ResourceConnection $resourceConnection
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Indexer\AbstractProcessor $indexerProcessor
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Indexer\AbstractProcessor $indexerProcessor
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->stockConfiguration = $stockConfiguration;
        $this->storeManager = $storeManager;
        $this->indexerProcessor = $indexerProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->update(
            $this->resourceConnection->getConnection()->getTableName('cataloginventory_stock_item'),
            ['website_id' => $this->stockConfiguration->getDefaultScopeId()],
            ['website_id = ?' => $this->storeManager->getWebsite()->getId()]
        );
        $this->indexerProcessor->getIndexer()->invalidate();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            CreateDefaultStock::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.2.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
