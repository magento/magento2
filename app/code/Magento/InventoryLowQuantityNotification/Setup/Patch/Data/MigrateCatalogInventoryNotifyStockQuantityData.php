<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Setup\Patch\Data;

use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

/**
 * Copy notify_stock_qty data from cataloginventory_stock_item to inventory_low_stock_notification_configuration.
 */
class MigrateCatalogInventoryNotifyStockQuantityData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $defaultSource = $this->defaultSourceProvider->getCode();
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select();
        $select
            ->from(
                ['stock_item' => $this->moduleDataSetup->getTable(StockItem::ENTITY)],
                [
                    'source_item.' . SourceItemInterface::SOURCE_CODE,
                    'source_item.' . SourceItemInterface::SKU,
                    'stock_item.notify_stock_qty',
                ]
            )->join(
                ['product' => $this->moduleDataSetup->getTable('catalog_product_entity')],
                'product.entity_id = stock_item.product_id',
                []
            )->join(
                ['source_item' => $this->moduleDataSetup->getTable(SourceItem::TABLE_NAME_SOURCE_ITEM)],
                'source_item.sku = product.sku',
                []
            )->where(
                'stock_item.use_config_notify_stock_qty = 0'
            )->where(
                'source_item.' . SourceItemInterface::SOURCE_CODE . ' = ?',
                $defaultSource
            );

        $sql = $connection->insertFromSelect(
            $select,
            $this->moduleDataSetup->getTable('inventory_low_stock_notification_configuration')
        );

        $connection->query($sql);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
