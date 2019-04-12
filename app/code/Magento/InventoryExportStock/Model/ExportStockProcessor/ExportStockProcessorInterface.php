<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model\ExportStockProcessor;

/**
 * Interface StockExportProcessorInterface provides product stock data
 */
interface ExportStockProcessorInterface
{
    /**
     * Provides product stock data
     *
     * @param array $products
     * @param int $stockId
     * @return array
     */
    public function execute(array $products, int $stockId):array;
}
