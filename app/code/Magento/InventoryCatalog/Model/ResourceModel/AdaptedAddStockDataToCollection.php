<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Inventory\Indexer\IndexStructure;

/**
 * Adapt adding stock data to collection for MSI.
 */
class AdaptedAddStockDataToCollection
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $isFilterInStock
     * @param string $tableName
     */
    public function addStockDataToCollection($collection, $isFilterInStock, string $tableName)
    {
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
