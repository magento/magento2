<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySalesApi\Model\GetAssignedStockIdForWebsiteInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * @inheritdoc
 */
class GetAssignedStockIdForWebsite implements GetAssignedStockIdForWebsiteInterface
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
    public function execute(string $websiteCode): ?int
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_stock_sales_channel');

        $select = $connection->select()
            ->from($tableName, ['stock_id'])
            ->where('code = ?', $websiteCode)
            ->where('type = ?', SalesChannelInterface::TYPE_WEBSITE);

        $result = $connection->fetchCol($select);

        if (count($result) === 0) {
            return null;
        }
        return (int)reset($result);
    }
}
