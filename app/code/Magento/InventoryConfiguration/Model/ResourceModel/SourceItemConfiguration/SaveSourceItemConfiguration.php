<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;

/**
 * Implementation of SourceItem Quantity notification save multiple operation for specific db layer
 * Save Multiple used here for performance efficient purposes over single save operation
 */
class SaveSourceItemConfiguration
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
    )
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Save the source Item configuration.
     *
     * @param SourceItemConfigurationInterface[] $configuration
     * @return void
     */
    public function execute(array $configuration)
    {
        if (!count($configuration)) {
            return;
        }
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItemConfiguration::TABLE_NAME_SOURCE_ITEM_CONFIGURATION);

        $columnsSql = $this->buildColumnsSqlPart([
            SourceItemConfigurationInterface::SOURCE_ITEM_ID,
            SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY
        ]);

        $valuesSql = $this->buildValuesSqlPart($configuration);
        $onDuplicateSql = $this->buildOnDuplicateSqlPart([
            SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY
        ]);
        $bind = $this->getSqlBindData($configuration);

        $insertSql = sprintf(
            'INSERT INTO %s (%s) VALUES %s %s',
            $tableName,
            $columnsSql,
            $valuesSql,
            $onDuplicateSql
        );
        $connection->query($insertSql, $bind);
    }

    /**
     * @param array $columns
     * @return string
     */
    private function buildColumnsSqlPart(array $columns): string
    {
        $connection = $this->resourceConnection->getConnection();
        $processedColumns = array_map([$connection, 'quoteIdentifier'], $columns);
        $sql = implode(', ', $processedColumns);
        return $sql;
    }

    /**
     * @param SourceItemInterface[] $sourceItems
     * @return string
     */
    private function buildValuesSqlPart(array $sourceItems): string
    {
        $sql = rtrim(str_repeat('(?, ?), ', count($sourceItems)), ', ');
        return $sql;
    }

    /**
     * @param SourceItemInterface[] $sourceItems
     * @return array
     */
    private function getSqlBindData(array $sourceItems): array
    {
        $bind = [];
        foreach ($sourceItems as $sourceItem) {
            $bind = array_merge($bind, [
                $sourceItem->getSourceItemId(),
                $sourceItem->getNotifyStockQty()
            ]);
        }
        return $bind;
    }

    /**
     * @param array $fields
     * @return string
     */
    private function buildOnDuplicateSqlPart(array $fields): string
    {
        $connection = $this->resourceConnection->getConnection();
        $processedFields = [];
        foreach ($fields as $field) {
            $processedFields[] = sprintf('%1$s = VALUES(%1$s)', $connection->quoteIdentifier($field));
        }
        $sql = 'ON DUPLICATE KEY UPDATE ' . implode(', ', $processedFields);
        return $sql;
    }
}