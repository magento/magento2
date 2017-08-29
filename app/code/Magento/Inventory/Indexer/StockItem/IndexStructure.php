<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Indexer\IndexStructureInterface;

/**
 * Index structure is responsible for index structure
 */
class IndexStructure implements IndexStructureInterface
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
    public function delete($index, array $dimensions = [])
    {
        $connection = $this->resourceConnection->getConnection();
        if ($connection->isTableExists($index)) {
            $connection->dropTable($index);
        }
    }

    /**
     * @inheritdoc
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        $table = $this->resourceConnection->getConnection()->newTable($index)->setComment(
            'Inventory Stock item Table'
        )->addColumn(
            'stock_item_id',
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true,
            ],
            'Stock Item ID'
        )->addColumn(
            StockItemInterface::STOCK_ID,
            Table::TYPE_INTEGER,
            null,
            [
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
            ],
            'Stock ID'
        )->addColumn(
            'sku',
            Table::TYPE_TEXT,
            64,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Sku'
        )->addColumn(
            'quantity',
            Table::TYPE_DECIMAL,
            null,
            [
                Table::OPTION_UNSIGNED => false,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0,
            ],
            'Quantity'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            [
                Table::OPTION_NULLABLE => true,
                Table::OPTION_UNSIGNED => true,
            ],
            'Status'
        )->addIndex(
            'idx_sku_stock_id',
            ['sku', StockItemInterface::STOCK_ID],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        );

        $this->resourceConnection->getConnection()->createTable($table);
    }
}
