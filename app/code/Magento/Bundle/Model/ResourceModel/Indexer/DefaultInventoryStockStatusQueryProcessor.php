<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class DefaultInventoryStockStatusQueryProcessor implements StockStatusQueryProcessorInterface
{
    /**
     * Apply stock status filter to the Select
     *
     * @param Select $select
     * @return Select
     */
    public function execute(Select $select): Select
    {
        return $select;
    }
}
