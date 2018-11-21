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
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
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
    }
}
