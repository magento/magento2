<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\StockSourceLink;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;
use Magento\Inventory\Model\StockSourceLink;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

/**
 * Implementation of StockSourceLink save multiple operation for specific db layer
 * Save Multiple used here for performance efficient purposes over single save operation
 */
class SaveMultiple
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
     * Multiple save StockSourceLinks
     *
     * @param StockSourceLinkInterface[] $links
     * @return void
     */
    public function execute(array $links)
    {
        if (!count($links)) {
            return;
        }
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(
            StockSourceLinkResourceModel::TABLE_NAME_STOCK_SOURCE_LINK
        );

        $columnsSql = $this->buildColumnsSqlPart([
            StockSourceLink::SOURCE_CODE,
            StockSourceLink::STOCK_ID,
            StockSourceLink::PRIORITY,
        ]);
        $valuesSql = $this->buildValuesSqlPart($links);
        $onDuplicateSql = $this->buildOnDuplicateSqlPart([
            StockSourceLink::PRIORITY,
        ]);
        $bind = $this->getSqlBindData($links);

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
        return implode(', ', $processedColumns);
    }

    /**
     * @param StockSourceLinkInterface[] $links
     * @return string
     */
    private function buildValuesSqlPart(array $links): string
    {
        $sql = rtrim(str_repeat('(?, ?, ?), ', count($links)), ', ');
        return $sql;
    }

    /**
     * @param StockSourceLinkInterface[] $links
     * @return array
     */
    private function getSqlBindData(array $links): array
    {
        $bind = [];
        foreach ($links as $link) {
            $bind = array_merge($bind, [
                $link->getSourceCode(),
                $link->getStockId(),
                $link->getPriority(),
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
        return 'ON DUPLICATE KEY UPDATE ' . implode(', ', $processedFields);
    }
}
