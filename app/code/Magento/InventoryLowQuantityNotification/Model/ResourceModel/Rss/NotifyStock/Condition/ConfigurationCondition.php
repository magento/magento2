<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock\Condition;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class ConfigurationCondition implements LowStockConditionInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $configuration;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        ResourceConnection $resourceConnection
    ) {
        $this->configuration = $stockConfiguration;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function execute(): string
    {
        $configManageStock = $this->configuration->getManageStock();
        $configNotifyStockQty = $this->configuration->getNotifyStockQty();

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

        return '(' . $condition . ')';
    }
}
