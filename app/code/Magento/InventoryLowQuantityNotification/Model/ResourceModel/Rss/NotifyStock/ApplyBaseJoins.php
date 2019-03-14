<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Inventory\Model\ResourceModel\Source;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;

class ApplyBaseJoins
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Select $select
     *
     * @return void
     */
    public function execute(Select $select)
    {
        $configurationJoinCondition =
            'source_item_config.' . SourceItemConfigurationInterface::SKU . ' = product.' . ProductInterface::SKU . ' '
            . Select::SQL_AND
            . ' source_item_config.' . SourceItemConfigurationInterface::SOURCE_CODE
            . ' = main_table.' . SourceItemInterface::SOURCE_CODE;

        $select->join(
            ['source' => $this->resourceConnection->getTableName(Source::TABLE_NAME_SOURCE)],
            'source.' . SourceInterface::SOURCE_CODE . ' = main_table.' . SourceItemInterface::SOURCE_CODE,
            ['source_name' => 'source.' . SourceInterface::NAME]
        )->join(
            ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'main_table.' . SourceItemInterface::SKU . ' = product.' . ProductInterface::SKU,
            ['*']
        )->join(
            [
                'source_item_config' => $this->resourceConnection->getTableName(
                    'inventory_low_stock_notification_configuration'
                )
            ],
            $configurationJoinCondition,
            ['source_item_config.' . SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY]
        )->join(
            ['legacy_stock_item' => $this->resourceConnection->getTableName('cataloginventory_stock_item')],
            'legacy_stock_item.product_id = product.entity_id',
            [
                'legacy_stock_item.' . StockItemInterface::LOW_STOCK_DATE,
                'use_config' => 'legacy_stock_item.' . StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY
            ]
        )->group('main_table.' . SourceItem::ID_FIELD_NAME);
    }
}
