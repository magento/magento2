<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
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
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableProvider
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableProvider,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->stockIndexTableProvider = $stockIndexTableProvider;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @param Collection $collection
     * @param int $stockId
     *
     * @return void
     */
    public function execute($collection, int $stockId): void
    {
        if ($stockId === $this->defaultStockProvider->getId()) {
            $isSalableColumnName = 'is_in_stock';
            $resource = $collection->getResource();
            $collection->getSelect()->join(
                ['inventory_in_stock' => $resource->getTable('cataloginventory_stock_item')],
                sprintf('%s.entity_id = inventory_in_stock.product_id', Collection::MAIN_TABLE_ALIAS),
                [IndexStructure::IS_SALABLE => $isSalableColumnName]
            );
        } else {
            $tableName = $this->stockIndexTableProvider->execute($stockId);

            $collection->getSelect()->join(
                ['inventory_in_stock' => $tableName], 'e.sku = inventory_in_stock.sku', []
            )->where('inventory_in_stock.' . IndexStructure::IS_SALABLE . ' = ?', 1);
        }
    }
}
