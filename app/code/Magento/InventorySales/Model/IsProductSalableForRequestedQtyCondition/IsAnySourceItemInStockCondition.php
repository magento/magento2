<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventorySales\Model\IsProductSalableCondition\IsAnySourceItemInStockCondition as IsAnySourceItemInStock;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;

/**
 * @inheritDoc
 */
class IsAnySourceItemInStockCondition implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var IsAnySourceItemInStock
     */
    private $isAnySourceInStockCondition;

    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    /**
     * @param IsAnySourceItemInStock $isAnySourceInStockCondition
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     */
    public function __construct(
        IsAnySourceItemInStock $isAnySourceInStockCondition,
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory
    ) {
        $this->isAnySourceInStockCondition = $isAnySourceInStockCondition;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->productSalableResultFactory = $productSalableResultFactory;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId, float $requestedQty): ProductSalableResultInterface
    {
        $isValid = $this->isAnySourceInStockCondition->execute($sku, $stockId);
        if ($isValid) {
            return $this->productSalableResultFactory->create(['errors' => []]);
        }
        $errors = [
            $this->productSalabilityErrorFactory->create([
                'code' => 'is_any_source_item_in_stock-no_source_items_in_stock',
                'message' => __('There are no source items with the in stock status')
            ])
        ];

        return $this->productSalableResultFactory->create(['errors' => $errors]);
    }
}
