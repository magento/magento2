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
use Magento\InventorySales\Model\IsProductSalableCondition\IsAnySourceInStockCondition
    as IsAnySourceInStockConditionCondition;

/**
 * @inheritdoc
 */
class IsAnySourceInStockCondition implements IsProductSalableForRequestedQtyInterface
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
     * IsAnySourceInStockCondition constructor.
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
        $isValid = $this->isAnySourceInStockCondition->execute($sku, $stockId);
        if (!$isValid) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'stock_item_is_any_source_in_stock-no_sources_in_stock',
                    'message' => __('There is no sources in stock')
                ])
            ];
            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }

        return $this->productSalableResultFactory->create(['errors' => []]);
    }
}
