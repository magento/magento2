<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class DefaultInventoryStockStatusQueryProcessor implements StockStatusQueryProcessorInterface
{
    /**
     * @param ResourceConnection $resource
     */
    public function __construct(private readonly ResourceConnection $resource)
    {
    }

    /**
     * Apply stock status filter to the Select
     *
     * @param Select $select
     * @return Select
     */
    public function execute(Select $select): Select
    {
        $select->join(
            ['si' => $this->resource->getTableName('cataloginventory_stock_status')],
            'si.product_id = bs.product_id',
            []
        )
        ->where('stock_status = ?', Stock::STOCK_IN_STOCK);
        return $select;
    }
}
