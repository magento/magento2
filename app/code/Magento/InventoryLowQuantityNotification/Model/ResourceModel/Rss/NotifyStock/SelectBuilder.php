<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\DB\Select;
use Magento\Inventory\Model\ResourceModel\Source;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock\Condition\LowStockConditionInterface;
use Magento\InventoryLowQuantityNotification\Setup\Operation\CreateSourceConfigurationTable;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;

class SelectBuilder
{
    /**
     * @var LowStockConditionInterface
     */
    private $lowStockCondition;

    /**
     * @param LowStockConditionInterface $lowStockCondition
     */
    public function __construct(
        LowStockConditionInterface $lowStockCondition
    ) {
        $this->lowStockCondition = $lowStockCondition;
    }

    /**
     * @param Select $select
     *
     * @return void
     */
    public function build(Select $select)
    {
        $connection = $select->getConnection();
        $condition = $this->lowStockCondition->execute();
        $sourceItemConfigurationTable = CreateSourceConfigurationTable::TABLE_NAME_SOURCE_ITEM_CONFIGURATION;
        $configurationJoinCondition =
            'source_item_config.' . SourceItemConfigurationInterface::SKU . ' = product.' . ProductInterface::SKU . ' '
            . Select::SQL_AND
            . ' source_item_config.' . SourceItemConfigurationInterface::SOURCE_CODE
            . ' = main_table.' . SourceItemInterface::SOURCE_CODE;

        $select->join(
            ['source' => $connection->getTableName(Source::TABLE_NAME_SOURCE)],
            'source.' . SourceInterface::SOURCE_CODE . '= main_table.' . SourceItemInterface::SOURCE_CODE,
            ['source_name' => 'source.' . SourceInterface::NAME]
        )->join(
            ['product' => $connection->getTableName('catalog_product_entity')],
            'main_table.' . SourceItemInterface::SKU . ' = product.' . ProductInterface::SKU,
            ['*']
        )->join(
            ['source_item_config' => $connection->getTableName($sourceItemConfigurationTable)],
            $configurationJoinCondition,
            ['source_item_config.' . SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY]
        )->join(
            ['invtr' => $connection->getTableName('cataloginventory_stock_item')],
            'invtr.product_id = product.entity_id',
            [
                'invtr.' . StockItemInterface::LOW_STOCK_DATE,
                'use_config' => 'invtr.' . StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY
            ]
        )->join(
            ['product_varchar' => $connection->getTableName('catalog_product_entity_varchar')],
            'product_varchar.entity_id = product.entity_id',
            ['name' => 'product_varchar.value']
        )->join(
            ['product_int' => $connection->getTableName('catalog_product_entity_int')],
            'product_int.entity_id = product.entity_id',
            ['status' => 'product_int.value']
        )
            ->where($condition)
            ->group('main_table.' . SourceItem::ID_FIELD_NAME);
    }
}
