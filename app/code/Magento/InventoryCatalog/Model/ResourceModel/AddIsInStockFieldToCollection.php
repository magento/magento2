<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

/**
 * Adapt adding and applying is in stock field to collection for Multi Stocks.
 */
class AddIsInStockFieldToCollection
{
    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableProvider;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableProvider
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableProvider
    ) {
        $this->stockIndexTableProvider = $stockIndexTableProvider;
    }

    /**
     * @param Collection $collection
     * @param int $stockId
     * @return void
     */
    public function execute($collection, int $stockId)
    {
        $tableName = $this->stockIndexTableProvider->execute($stockId);

        $collection->getSelect()->join(
            ['inventory_in_stock' => $tableName],
            'e.sku = inventory_in_stock.sku',
            []
        )->where('inventory_in_stock.' . IndexStructure::IS_SALABLE . ' = ?', 1);
    }
}
