<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Model\IsProductAssignedToStockInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForSkuInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Class Provides stock data with reservation taken into in account
 */
class PreciseExportStockProcessor
{
    /**
     * @var IsSourceItemManagementAllowedForSkuInterface
     */
    private $isSourceItemManagementAllowedForSku;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetQtyForNotManageStock
     */
    private $getQtyForNotManageStock;
    /**
     * @var IsProductAssignedToStockInterface
     */
    private $isProductAssignedToStock;
    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @param IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param GetQtyForNotManageStock $getQtyForNotManageStock
     * @param IsProductAssignedToStockInterface $isProductAssignedToStock
     * @param IsProductSalableInterface $isProductSalable
     */
    public function __construct(
        IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku,
        GetProductSalableQtyInterface $getProductSalableQty,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetQtyForNotManageStock $getQtyForNotManageStock,
        IsProductAssignedToStockInterface $isProductAssignedToStock,
        IsProductSalableInterface $isProductSalable
    ) {
        $this->isSourceItemManagementAllowedForSku = $isSourceItemManagementAllowedForSku;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getQtyForNotManageStock = $getQtyForNotManageStock;
        $this->isProductAssignedToStock = $isProductAssignedToStock;
        $this->isProductSalable = $isProductSalable;
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
        $skus = $this->getProductSkus($products);
        $items = [];
        foreach ($skus as $sku) {
            try {
                $qty = $this->getProductSalableQtyByStock($sku, $stockId);
                $isSalable = $this->isProductSalable->execute($sku, $stockId);
            } catch (SkuIsNotAssignedToStockException $e) {
                continue;
            }

            $items[] = [
                'sku' => $sku,
                'qty' => $qty,
                'is_salable' => $isSalable
            ];
        }

        return $items;
    }

    /**
     * Extracts product skus from $product array
     *
     * @param array $products
     * @return array
     */
    private function getProductSkus(array $products): array
    {
        $skus = [];
        /** @var ProductInterface $product */
        foreach ($products as $product) {
            $skus[] = $product->getSku();
        }

        return $skus;
    }

    /**
     * Provides qty by stock and sku
     *
     * @param string $sku
     * @param int $stockId
     * @return float|null
     * @throws InputException
     * @throws LocalizedException
     * @throws SkuIsNotAssignedToStockException
     */
    private function getProductSalableQtyByStock(string $sku, int $stockId): ?float
    {
        if (!$this->isProductAssignedToStock->execute($sku, $stockId)) {
            throw new SkuIsNotAssignedToStockException(__('The requested sku is not assigned to given stock.'));
        }
        if (!$this->getStockItemConfiguration->execute($sku, $stockId)->isManageStock()) {
            return (float)$this->getQtyForNotManageStock->execute();
        }
        if (!$this->isSourceItemManagementAllowedForSku->execute($sku)) {
            return null;
        }

        return $this->getProductSalableQty->execute($sku, $stockId);
    }
}
