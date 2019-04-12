<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model\ExportStockProcessor;

use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForSkuInterface;
use Magento\InventoryExportStock\Model\GetQtyForNotManageStock;
use Magento\InventoryExportStock\Model\ResourceModel\GetStockIndexDump;

/**
 * Class StockIndexDumpProcessor provides sku and qty of products dumping them from stock index table
 */
class StockIndexDumpProcessor implements ExportStockProcessorInterface
{
    public const PROCESSOR_TYPE = 'stock_dump';

    /**
     * @var GetQtyForNotManageStock
     */
    private $getQtyForNotManageStock;

    /**
     * @var IsSourceItemManagementAllowedForSkuInterface
     */
    private $isSourceItemManagementAllowedForSku;

    /**
     * @var GetStockIndexDump
     */
    private $getStockIndexDump;

    /**
     * GetStockIndexDumpProcessor constructor
     *
     * @param GetQtyForNotManageStock $getQtyForNotManageStock
     * @param IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku
     * @param GetStockIndexDump $getStockIndexDump
     */
    public function __construct(
        GetQtyForNotManageStock $getQtyForNotManageStock,
        IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku,
        GetStockIndexDump $getStockIndexDump
    ) {
        $this->getQtyForNotManageStock = $getQtyForNotManageStock;
        $this->isSourceItemManagementAllowedForSku = $isSourceItemManagementAllowedForSku;
        $this->getStockIndexDump = $getStockIndexDump;
    }

    /**
     * Provides sku and qty of products dumping them from stock index table
     *
     * @param array $products
     * @param int $stockId
     * @return array
     */
    public function execute(array $products, int $stockId): array
    {
        $qtyForNotManageStock = $this->getQtyForNotManageStock->execute();
        $productStockIndex = $this->getStockIndexDump->execute($products, $stockId);
        $items = [];
        foreach ($productStockIndex as $index) {
            if ($this->isSourceItemManagementAllowedForSku->execute($index['sku'])) {
                $qty = $index['qty'] ?: $qtyForNotManageStock;
            } else {
                $qty = null;
            }
            $items[] = [
                'sku' => $index['sku'],
                'qty' => $qty
            ];
        }

        return $items;
    }
}
