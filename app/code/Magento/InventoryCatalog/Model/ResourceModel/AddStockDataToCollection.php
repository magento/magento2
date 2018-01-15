<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Inventory\Indexer\IndexStructure;
use Magento\Inventory\Model\StockIndexTableProviderInterface;

/**
 * Add Stock data to collection
 */
class AddStockDataToCollection
{
    /**
     * @var StockIndexTableProviderInterface
     */
    private $stockIndexTableProvider;

    /**
     * @param StockIndexTableProviderInterface $stockIndexTableProvider
     */
    public function __construct(StockIndexTableProviderInterface $stockIndexTableProvider)
    {
        $this->stockIndexTableProvider = $stockIndexTableProvider;
    }

    /**
     * @param Collection $collection
     * @param bool $isFilterInStock
     * @param int $stockId
     */
    public function addStockDataToCollection(Collection $collection, bool $isFilterInStock, int $stockId)
    {
        $tableName = $this->stockIndexTableProvider->execute($stockId);
        $method = $isFilterInStock ? 'join' : 'joinLeft';

        $isSalableExpression = $collection->getConnection()->getCheckSql(
            'stock_status_index.' . IndexStructure::QUANTITY . ' > 0',
            1,
            0
        );
        $collection->getSelect()->$method(
            ['stock_status_index' => $tableName],
            'e.sku = stock_status_index.' . IndexStructure::SKU,
            ['is_salable' => $isSalableExpression]
        );

        if ($isFilterInStock) {
            $collection->getSelect()->where(
                'stock_status_index.' . IndexStructure::QUANTITY . ' > 0'
            );
        }
    }
}
