<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model\ExportStockProcessor;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForSkuInterface;
use Magento\InventoryExportStock\Model\GetQtyForNotManageStock;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

/**
 * Class Provides precise method of getting stock data
 */
class PreciseStockProcessor implements ExportStockProcessorInterface
{
    public const PROCESSOR_TYPE = 'precise';

    /**
     * @var IsSourceItemManagementAllowedForSkuInterface
     */
    private $isSourceItemManagementAllowedForSku;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var GetQtyForNotManageStock
     */
    private $getQtyForNotManageStock;

    /**
     * PreciseStockProcessor constructor
     *
     * @param IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku
     * @param GetQtyForNotManageStock $getQtyForNotManageStock
     * @param GetProductSalableQtyInterface $getProductSalableQty
     */
    public function __construct(
        IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku,
        GetQtyForNotManageStock $getQtyForNotManageStock,
        GetProductSalableQtyInterface $getProductSalableQty
    ) {
        $this->isSourceItemManagementAllowedForSku = $isSourceItemManagementAllowedForSku;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getQtyForNotManageStock = $getQtyForNotManageStock;
    }

    /**
     * Provides precise method for getting stock data
     *
     * @param array $products
     * @param int $stockId
     * @return array
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute(array $products, int $stockId): array
    {
        $qtyForNotManageStock = $this->getQtyForNotManageStock->execute();
        $items = [];
        foreach ($products as $product) {
            $sku = $product->getSku();
            if ($this->isSourceItemManagementAllowedForSku->execute($sku)) {
                $qty = $this->getProductSalableQty->execute($sku, $stockId) ?: $qtyForNotManageStock;
            } else {
                $qty = null;
            }

            $items[] = [
                'sku' => $sku,
                'qty' => $qty
            ];
        }

        return $items;
    }
}
