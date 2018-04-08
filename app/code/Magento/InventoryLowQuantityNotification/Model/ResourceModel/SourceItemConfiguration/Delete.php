<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * Implementation of SourceItem Configuration delete operation for specific db layer
 */
class Delete
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
     * @param string $sourceCode
     * @param string $sku
     * @return void
     */
    public function execute(string $sourceCode, string $sku)
    {
        $connection = $this->resourceConnection->getConnection();
        $sourceItemConfigurationTable = $this->resourceConnection
            ->getTableName('inventory_low_stock_notification_configuration');

        $connection->delete($sourceItemConfigurationTable, [
            SourceItemConfigurationInterface::SOURCE_CODE . ' = ?' => $sourceCode,
            SourceItemConfigurationInterface::SKU . ' = ?' => $sku,
        ]);
    }
}
