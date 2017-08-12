<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer\StockItem;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Indexer\IndexStructureInterface;

/**
 * @todo add comment
 */
class IndexStructure implements IndexStructureInterface
{

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }


    /**
     * @inheritdoc
     */
    public function delete($index, array $dimensions = [])
    {
        if ($this->resource->getConnection()->isTableExists($index)) {
            $this->resource->getConnection()->dropTable($index);
        }
    }

    /**
     * @inheritdoc
     */
    public function create($index, array $fields, array $dimensions = [])
    {
        $table = $this->resource->getConnection()->newTable($index)->setComment(
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
        );
        $this->resource->getConnection()->createTable($table);
    }
}
