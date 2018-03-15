<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Inventory\Model\ResourceModel\Source;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryLowQuantityNotification\Setup\Operation\CreateSourceConfigurationTable;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\Store\Model\StoreManagerInterface;

class SelectBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Status
     */
    private $productStatus;

    /**
     * @param ResourceConnection $resourceConnection
     * @param StockConfigurationInterface $stockConfiguration
     * @param EavConfig $eavConfig
     * @param StoreManagerInterface $storeManager
     * @param Status $productStatus
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StockConfigurationInterface $stockConfiguration,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager,
        Status $productStatus
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->stockConfiguration = $stockConfiguration;
        $this->storeManager = $storeManager;
        $this->eavConfig = $eavConfig;
        $this->productStatus = $productStatus;
    }

    /**
     * @param Select $select
     *
     * @return void
     */
    public function build(Select $select)
    {
        $sourceItemConfigurationTable = CreateSourceConfigurationTable::TABLE_NAME_SOURCE_ITEM_CONFIGURATION;
        $configurationJoinCondition =
            'source_item_config.' . SourceItemConfigurationInterface::SKU . ' = product.' . ProductInterface::SKU . ' '
            . Select::SQL_AND
            . ' source_item_config.' . SourceItemConfigurationInterface::SOURCE_CODE
            . ' = main_table.' . SourceItemInterface::SOURCE_CODE;

        $select->join(
            ['source' => $this->resourceConnection->getTableName(Source::TABLE_NAME_SOURCE)],
            'source.' . SourceInterface::SOURCE_CODE . '= main_table.' . SourceItemInterface::SOURCE_CODE,
            ['source_name' => 'source.' . SourceInterface::NAME]
        )->join(
            ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'main_table.' . SourceItemInterface::SKU . ' = product.' . ProductInterface::SKU,
            ['*']
        )->join(
            ['source_item_config' => $this->resourceConnection->getTableName($sourceItemConfigurationTable)],
            $configurationJoinCondition,
            ['source_item_config.' . SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY]
        )->join(
            ['invtr' => $this->resourceConnection->getTableName('cataloginventory_stock_item')],
            'invtr.product_id = product.entity_id',
            [
                'invtr.' . StockItemInterface::LOW_STOCK_DATE,
                'use_config' => 'invtr.' . StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY
            ]
        )->join(
            ['product_varchar' => $this->resourceConnection->getTableName('catalog_product_entity_varchar')],
            'product_varchar.entity_id = product.entity_id',
            ['name' => 'product_varchar.value']
        )->join(
            ['product_int' => $this->resourceConnection->getTableName('catalog_product_entity_int')],
            'product_int.entity_id = product.entity_id',
            ['status' => 'product_int.value']
        )
            ->group('main_table.' . SourceItem::ID_FIELD_NAME);

        $this->applyConfigurationCondition($select);
        $this->applyNameAttributeCondition($select);
        $this->applyStatusAttributeCondition($select);
    }

    /**
     * Apply manage_stock and min_qty condition to select.
     *
     * @param Select $select
     */
    private function applyConfigurationCondition(Select $select)
    {
        $configManageStock = $this->stockConfiguration->getManageStock();
        $configNotifyStockQty = $this->stockConfiguration->getNotifyStockQty();

        $connection = $this->resourceConnection->getConnection();
        $qtyCondition = $connection->getIfNullSql(
            'source_item_config.notify_stock_qty',
            $configNotifyStockQty
        );

        $globalManageStockEnabledCondition = implode(
            [
                $connection->prepareSqlCondition('invtr.use_config_manage_stock', 1),
                $connection->prepareSqlCondition($configManageStock, 1),
                $connection->prepareSqlCondition('main_table.quantity', ['lt' => $qtyCondition]),
            ],
            ' ' . Select::SQL_AND . ' '
        );
        $globalManageStockDisabledCondition = implode(
            [
                $connection->prepareSqlCondition('invtr.use_config_manage_stock', 0),
                $connection->prepareSqlCondition('invtr.manage_stock', 1),
                $connection->prepareSqlCondition('main_table.quantity', ['lt' => $qtyCondition]),
            ],
            ' ' . Select::SQL_AND . ' '
        );

        $condition = implode(
            [
                $globalManageStockEnabledCondition,
                $globalManageStockDisabledCondition,
            ],
            ') ' . Select::SQL_OR . ' ('
        );

        $select->where('(' . $condition . ')');
    }

    /**
     * Apply condition for attribute name to select.
     *
     * @param Select $select
     */
    private function applyNameAttributeCondition(Select $select)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $attributeId = $this->eavConfig->getAttribute(Product::ENTITY, ProductInterface::NAME)->getAttributeId();
        $connection = $this->resourceConnection->getConnection();

        $condition = implode(
            [
                $connection->prepareSqlCondition('product_varchar.store_id', $storeId),
                $connection->prepareSqlCondition('product_varchar.attribute_id', $attributeId),
            ],
            ' ' . Select::SQL_AND . ' '
        );

        $select->where($condition);
    }

    /**
     * Apply condition for attribute status to select.
     *
     * @param Select $select
     */
    private function applyStatusAttributeCondition(Select $select)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $attributeId = $this->eavConfig->getAttribute(Product::ENTITY, ProductInterface::STATUS)->getAttributeId();
        $connection = $this->resourceConnection->getConnection();
        $statusVisibilityCondition = $connection->prepareSqlCondition(
            'product_int.value',
            ['in' => $this->productStatus->getVisibleStatusIds()]
        );
        $condition = implode(
            [
                $statusVisibilityCondition,
                $connection->prepareSqlCondition('product_int.store_id', $storeId),
                $connection->prepareSqlCondition('product_int.attribute_id', $attributeId),
            ],
            ' ' . Select::SQL_AND . ' '
        );

        $select->where($condition);
    }
}
