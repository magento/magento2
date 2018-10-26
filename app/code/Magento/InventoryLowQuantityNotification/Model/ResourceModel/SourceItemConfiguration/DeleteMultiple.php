<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * Implementation of SourceItem Configuration delete operation for specific db layer
 */
class DeleteMultiple
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * DeleteMultiple constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Delete multiple notification configurations
     *
     * @param SourceItemInterface[] $sourceItems
     * @return void
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function execute(array $sourceItems)
    {
        if (!empty($sourceItems)) {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection
                ->getTableName('inventory_low_stock_notification_configuration');

            $whereSql = $this->buildWhereSqlPart($sourceItems);
            $connection->delete($tableName, $whereSql);
        }
    }

    /**
     * Build a SQL where from an array of source items
     *
     * @param SourceItemInterface[] $sourceItems
     * @return string
     */
    private function buildWhereSqlPart(array $sourceItems): string
    {
        $connection = $this->resourceConnection->getConnection();

        $condition = [];
        foreach ($sourceItems as $sourceItem) {
            $skuCondition = $connection->quoteInto(
                SourceItemConfigurationInterface::SKU . ' = ?',
                $sourceItem->getSku()
            );
            $sourceCodeCondition = $connection->quoteInto(
                SourceItemConfigurationInterface::SOURCE_CODE . ' = ?',
                $sourceItem->getSourceCode()
            );
            $condition[] = '(' . $skuCondition . ' AND ' . $sourceCodeCondition . ')';
        }

        return implode(' OR ', $condition);
    }
}
