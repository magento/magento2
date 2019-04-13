<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\InventorySales\Model\IsProductSalableCondition\IsAnySourceItemInStockCondition
    as IsAnySourceInStockConditionCondition;

/**
 * @inheritdoc
 */
class IsAnySourceItemInStockCondition implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var IsAnySourceInStockConditionCondition
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
     * @param IsAnySourceInStockConditionCondition $isAnySourceInStockCondition
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     */
    public function __construct(
        IsAnySourceInStockConditionCondition $isAnySourceInStockCondition,
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
        $errors = [];
        $isValid = $this->isAnySourceInStockCondition->execute($sku, $stockId);
        if (!$isValid) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'stock_item_is_any_source_in_stock-no_source_items_in_stock',
                    'message' => __('There are no source items with in stock status')
                ])
            ];
        }

        return $this->productSalableResultFactory->create(['errors' => $errors]);
    }
}
