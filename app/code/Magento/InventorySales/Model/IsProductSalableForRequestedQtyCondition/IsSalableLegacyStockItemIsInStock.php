<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventorySales\Model\IsProductSalableCondition\IsSalableLegacyStockItemIsInStock
    as IsSalableLegacyStockItem;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;

/**
 * @inheritdoc
 */
class IsSalableLegacyStockItemIsInStock implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    /**
     * @var IsSalableLegacyStockItem
     */
    private $isSalableLegacyStockItemIsInStock;

    /**
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     * @param IsSalableLegacyStockItem $isSalableLegacyStockItemIsInStock
     */
    public function __construct(
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        IsSalableLegacyStockItem $isSalableLegacyStockItemIsInStock

    ) {
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->isSalableLegacyStockItemIsInStock = $isSalableLegacyStockItemIsInStock;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId, float $requestedQty): ProductSalableResultInterface
    {
        $isInStock = $this->isSalableLegacyStockItemIsInStock->execute($sku, $stockId);

        if (!$isInStock) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_in_stock-false',
                    'message' => __('This product is out of stock.')
                ])
            ];
            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }
        return $this->productSalableResultFactory->create(['errors' => []]);
    }
}
