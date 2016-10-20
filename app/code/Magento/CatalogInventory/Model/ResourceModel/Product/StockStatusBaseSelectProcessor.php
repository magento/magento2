<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Class StockStatusBaseSelectProcessor
 */
class StockStatusBaseSelectProcessor implements BaseSelectProcessorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Add stock item filter to selects
     *
     * @param Select $select
     * @return Select
     */
    public function process(Select $select)
    {
        $stockStatusTable = $this->resource->getTableName('cataloginventory_stock_status');

        /** @var Select $select */
        $select->join(
            ['stock' => $stockStatusTable],
            sprintf('stock.product_id = %s.entity_id', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
            []
        )
            ->where('stock.stock_status = ?', Stock::STOCK_IN_STOCK);
        return $select;
    }
}
