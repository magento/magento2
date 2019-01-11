<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySalesApi\Model\DeleteSalesChannelToStockLinkInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySales\Model\StockBySalesChannelCache;

/**
 * Implementation of link deleting between Stock and Sales Channels for specific db layer
 *
 * There is no additional business logic on SPI (Service Provider Interface) level so could use resource model as
 * SPI implementation directly
 */
class DeleteSalesChannelToStockLink implements DeleteSalesChannelToStockLinkInterface
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
     * @inheritdoc
     */
    public function execute(string $type, string $code): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_stock_sales_channel');

        $connection->delete($tableName, [
            SalesChannelInterface::TYPE . ' = ?' => $type,
            SalesChannelInterface::CODE . ' = ?' => $code,
        ]);

        $this->stockBySalesChannelCache->clean();
    }
}
