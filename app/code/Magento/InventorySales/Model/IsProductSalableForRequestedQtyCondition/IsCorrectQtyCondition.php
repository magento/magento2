<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\Math\Division as MathDivision;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryReservations\Model\GetReservationsQuantityInterface;
use Magento\InventorySales\Model\GetStockItemDataInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;

/**
 * @inheritdoc
 *
 *
 */
class IsCorrectQtyCondition implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $getReservationsQuantity;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var StockConfigurationInterface
     */
    private $configuration;

    /**
     * @var MathDivision
     */
    protected $mathDivision;

    /**
     * @var ProductSalabilityErrorFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var IsProductSalableResultFactory
     */
    private $isProductSalableResultFactory;

    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        StockConfigurationInterface $configuration,
        GetReservationsQuantityInterface $getReservationsQuantity,
        GetStockItemDataInterface $getStockItemData,
        MathDivision $mathDivision,
        ProductSalabilityErrorFactory $productSalabilityErrorFactory,
        IsProductSalableResultFactory $isProductSalableResultFactory
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->configuration = $configuration;
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->mathDivision = $mathDivision;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->isProductSalableResultFactory = $isProductSalableResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId, float $requestedQty): IsProductSalableResultInterface
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        if (null === $stockItemConfiguration) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_correct_qty-no_config',
                    'message' => __('Missing stock item configuration')
                ])
            ];
            return $this->isProductSalableResultFactory->create(['errors' => $errors]);
        }

        // Out-of-Stock Threshold
        // TODO verify whether we should use < or <=
        $globalMinQty = $this->configuration->getMinQty();
        if (($stockItemConfiguration->isUseConfigMinQty() == 1 && $requestedQty < $globalMinQty)
            || ($stockItemConfiguration->isUseConfigMinQty() == 0
                && $requestedQty < $stockItemConfiguration->getMinQty()
            )) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_correct_qty-out_of_stock_threshold',
                    'message' => __('The requested qty is not available')
                ])
            ];
            return $this->isProductSalableResultFactory->create(['errors' => $errors]);
        }

        // Minimum Qty Allowed in Shopping Cart
        // TODO verify whether we should use < or <=
        $globalMinSaleQty = $this->configuration->getMinSaleQty();
        if (($stockItemConfiguration->isUseConfigMinSaleQty() == 1 && $requestedQty < $globalMinSaleQty)
            || ($stockItemConfiguration->isUseConfigMinSaleQty() == 0
                && $requestedQty < $stockItemConfiguration->getMinSaleQty()
            )) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_correct_qty-min_sale_qty',
                    'message' => __('The requested qty is less than the minimun qty allowed in shopping cart')
                ])
            ];
            return $this->isProductSalableResultFactory->create(['errors' => $errors]);
        }

        // Maximum Qty Allowed in Shopping Cart
        // TODO verify whether we should use > or >=
        $globalMaxSaleQty = $this->configuration->getMaxSaleQty();
        if (($stockItemConfiguration->isUseConfigMaxSaleQty() == 1 && $requestedQty > $globalMaxSaleQty)
            || ($stockItemConfiguration->isUseConfigMaxSaleQty() == 0
                && $requestedQty > $stockItemConfiguration->getMaxSaleQty()
            )) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_correct_qty-max_sale_qty',
                    'message' =>__('The requested qty exceeds the maximum qty allowed in shopping cart')
                ])
            ];
            return $this->isProductSalableResultFactory->create(['errors' => $errors]);
        }

        // Qty Increments
        if ($this->mathDivision->getExactDivision($requestedQty, $this->configuration->getQtyIncrements()) !== 0
            || $this->mathDivision->getExactDivision(
                $requestedQty,
                $stockItemConfiguration->getQtyIncrements()
            ) !== 0) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_correct_qty-qty_increment',
                    'message' => __('The requested qty is not a valid increment')
                ])
            ];
            return $this->isProductSalableResultFactory->create(['errors' => $errors]);
        }

        return $this->isProductSalableResultFactory->create(['errors' => []]);
    }
}
