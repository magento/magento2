<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryExportStock\Model\ResourceModel\StockIndexDumpProcessor;
use Magento\InventoryExportStockApi\Api\ExportStockIndexDataInterface;

/**
 * Class ExportStockIndexData
 */
class ExportStockIndexData implements ExportStockIndexDataInterface
{
    /**
     * @var StockIndexDumpProcessor
     */
    private $stockIndexDumpProcessor;

    /**
     * ExportStockIndexData constructor
     *
     * @param StockIndexDumpProcessor $stockIndexDumpProcessor
     */
    public function __construct(
        StockIndexDumpProcessor $stockIndexDumpProcessor
    ) {
        $this->stockIndexDumpProcessor = $stockIndexDumpProcessor;
    }

    /**
     * Provides stock index export from inventory_stock_% table
     *
     * @param int $stockId
     * @return array
     * @throws LocalizedException
     */
    public function execute(
        int $stockId
    ): array {
        try {
            $items = $this->stockIndexDumpProcessor->execute($stockId);
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $items;
    }
}
