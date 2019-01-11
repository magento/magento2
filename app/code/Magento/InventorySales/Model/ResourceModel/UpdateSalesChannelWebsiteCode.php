<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySales\Model\StockBySalesChannelCache;

/**
 * This class handles website code change and should not be used directly, but only
 * from \Magento\InventorySales\Plugin\Store\WebsiteResourcePlugin to keep a soft integrity
 * between 'store_website' table and 'inventory_stock_sales_channel' table on changes.
 */
class UpdateSalesChannelWebsiteCode
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockBySalesChannelCache
     */
    private $stockBySalesChannelCache;

    /**
     * @param ResourceConnection $resourceConnection
     * @param StockBySalesChannelCache $stockBySalesChannelCache
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StockBySalesChannelCache $stockBySalesChannelCache
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->stockBySalesChannelCache = $stockBySalesChannelCache;
    }

    /**
     * @param string $oldCode
     * @param string $newCode
     * @return void
     */
    public function execute(
        string $oldCode,
        string $newCode
    ): void {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_stock_sales_channel');

        $connection->update(
            $tableName,
            [
                SalesChannelInterface::CODE => $newCode,
            ],
            [
                SalesChannelInterface::TYPE . ' = ?' => SalesChannelInterface::TYPE_WEBSITE,
                SalesChannelInterface::CODE . ' = ?' => $oldCode,
            ]
        );

        $this->stockBySalesChannelCache->clean();
    }
}
