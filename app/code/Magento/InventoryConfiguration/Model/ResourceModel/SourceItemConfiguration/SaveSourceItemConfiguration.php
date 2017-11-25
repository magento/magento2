<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryConfiguration\Setup\Operation\CreateSourceConfigurationTable;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;

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
    ) {
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
        $tableName = $this->resourceConnection
            ->getTableName(CreateSourceConfigurationTable::TABLE_NAME_SOURCE_ITEM_CONFIGURATION);

        $columnsSql = $this->buildColumnsSqlPart([
            SourceItemConfigurationInterface::SOURCE_ID,
            SourceItemConfigurationInterface::SKU,
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
     * @param SourceItemInterface[] $sourceItemsConfigurations
     * @return string
     */
    private function buildValuesSqlPart(array $sourceItemsConfigurations): string
    {
        $sql = rtrim(str_repeat('(?, ?, ?), ', count($sourceItemsConfigurations)), ', ');
        return $sql;
    }

    /**
     * @param SourceItemInterface[] $sourceItemsConfiguration
     * @return array
     */
    private function getSqlBindData(array $sourceItemsConfiguration): array
    {
        $bind = [];
        /** @var SourceItemConfigurationInterface $sourceItemConfiguration */
        foreach ($sourceItemsConfiguration as $sourceItemConfiguration) {
            $bind = array_merge($bind, [
                $sourceItemConfiguration->getSourceId(),
                $sourceItemConfiguration->getSku(),
                $sourceItemConfiguration->getNotifyStockQty()
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
