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
 * Get configuration data for specific source item
 */
class GetData
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param string $sourceCode
     * @param string $sku
     * @return array|null
     */
    public function execute(string $sourceCode, string $sku)
    {
        $connection = $this->resourceConnection->getConnection();
        $sourceItemConfigurationTable = $this->resourceConnection
            ->getTableName('inventory_low_stock_notification_configuration');

        $select = $connection->select()
            ->from($sourceItemConfigurationTable)
            ->where(SourceItemConfigurationInterface::SOURCE_CODE . ' = ?', $sourceCode)
            ->where(SourceItemConfigurationInterface::SKU . ' = ?', $sku);

        $row = $connection->fetchRow($select);
        return $row ? $row : null;
    }
}
