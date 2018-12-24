<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\ObjectManager;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

/**
 * Add Stock data to collection
 */
class AddStockDataToCollection
{
    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        DefaultStockProviderInterface $defaultStockProvider = null
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->defaultStockProvider = $defaultStockProvider ?: ObjectManager::getInstance()
            ->get(DefaultStockProviderInterface::class);
    }

    /**
     * @param Collection $collection
     * @param bool $isFilterInStock
     * @param int $stockId
     * @return void
     */
    public function execute(Collection $collection, bool $isFilterInStock, int $stockId)
    {
        if ($stockId === $this->defaultStockProvider->getId()) {
            $isSalableColumnName = 'stock_status';
            $resource = $collection->getResource();
            $collection->getSelect()
                ->join(
                    ['stock_status_index' => $resource->getTable('cataloginventory_stock_status')],
                    sprintf('%s.entity_id = stock_status_index.product_id', Collection::MAIN_TABLE_ALIAS),
                    [IndexStructure::IS_SALABLE => $isSalableColumnName]
                );
        } else {
            $stockIndexTableName = $this->stockIndexTableNameResolver->execute($stockId);
            $resource = $collection->getResource();
            $collection->getSelect()->join(
                ['product' => $resource->getTable('catalog_product_entity')],
                sprintf('product.entity_id = %s.entity_id', Collection::MAIN_TABLE_ALIAS),
                []
            );
            $isSalableColumnName = IndexStructure::IS_SALABLE;
            $collection->getSelect()
                ->join(
                    ['stock_status_index' => $stockIndexTableName],
                    'product.sku = stock_status_index.' . IndexStructure::SKU,
                    [$isSalableColumnName]
                );
        }

        if ($isFilterInStock) {
            $collection->getSelect()
                ->where('stock_status_index.' . $isSalableColumnName . ' = ?', 1);
        }
    }
}
