<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Bulk configuration unassign resource model
 */
class BulkConfigurationUnassign
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
     * Bulk assign source items configurations from source items
     *
     * @param array $skus
     * @param array $sources
     */
    public function execute(
        array $skus,
        array $sources
    ) {
        $tableName = $this->resourceConnection->getTableName('inventory_low_stock_notification_configuration');
        $connection = $this->resourceConnection->getConnection();

        $connection->delete(
            $tableName,
            $connection->quoteInto('sku IN (?)', $skus) . ' AND ' .
            $connection->quoteInto('source_code IN (?)', $sources)
        );
    }
}
