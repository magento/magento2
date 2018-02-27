<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Setup;

use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryLowQuantityNotification\Setup\Operation\CreateSourceConfigurationTable;

/**
 * Copy notify_stock_qty data from cataloginventory_stock_item to inventory_low_stock_notification_configuration.
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    public function __construct(DefaultSourceProviderInterface $defaultSourceProvider)
    {
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $defaultSource = $this->defaultSourceProvider->getCode();
        $connection = $setup->getConnection();
        $select = $connection->select();
        $select
            ->from(
                ['stock_item' => $setup->getTable(StockItem::ENTITY)],
                [
                    'source_item.' . SourceItemInterface::SOURCE_CODE,
                    'source_item.' . SourceItemInterface::SKU,
                    'stock_item.notify_stock_qty',
                ]
            )->join(
                ['product' => $setup->getTable('catalog_product_entity')],
                'product.entity_id = stock_item.product_id',
                []
            )->join(
                ['source_item' => $setup->getTable(SourceItem::TABLE_NAME_SOURCE_ITEM)],
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
            $setup->getTable(CreateSourceConfigurationTable::TABLE_NAME_SOURCE_ITEM_CONFIGURATION)
        );

        $connection->query($sql);
    }
}
