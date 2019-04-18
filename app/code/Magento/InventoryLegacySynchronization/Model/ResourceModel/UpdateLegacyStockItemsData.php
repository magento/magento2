<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Update legacy stock items data in one single database operation
 */
class UpdateLegacyStockItemsData
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
     * @param array $legacyItemsData
     */
    public function execute(array $legacyItemsData): void
    {
        if (empty($legacyItemsData)) {
            return;
        }

        $tableName = $this->resourceConnection->getTableName('cataloginventory_stock_item');

        $connection = $this->resourceConnection->getConnection();
        $connection->insertOnDuplicate(
            $tableName,
            $legacyItemsData,
            [
                StockItemInterface::IS_IN_STOCK,
                StockItemInterface::QTY,
            ]
        );
    }
}
