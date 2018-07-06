<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;

/**
 * Allow min_qty to be assigned a value below 0.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class StockDataFilterPlugin
{
    public function aroundFilter(
        StockDataFilter $subject,
        callable $proceed,
        array $stockData
    ) {
        $originalStockData = $proceed($stockData);
        $originalStockData['min_qty'] = $stockData['min_qty'];
        return $originalStockData;
    }
}
