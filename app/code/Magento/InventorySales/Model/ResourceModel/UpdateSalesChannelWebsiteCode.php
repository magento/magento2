<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * This class handles website code change and should not be used directly, but only
 * from \Magento\InventorySales\Plugin\Store\WebsiteResourcePlugin to keep a soft integrity
 * between 'store_website' table and 'inventory_stock_sales_channel' table on changes.
 *
 * See: https://github.com/magento-engcom/msi/issues/1306
 */
class UpdateSalesChannelWebsiteCode
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * UpdateSalesChannelWebsiteCode constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string $oldCode
     * @param string $newCode
     */
    public function execute(
        string $oldCode,
        string $newCode
    ) {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_stock_sales_channel');

        $connection->update($tableName, [
            SalesChannelInterface::CODE => $newCode,
        ], [
            $connection->quoteInto(SalesChannelInterface::CODE . "=?", $oldCode),
            $connection->quoteInto(SalesChannelInterface::TYPE . "=?", SalesChannelInterface::TYPE_WEBSITE),
        ]);
    }
}
