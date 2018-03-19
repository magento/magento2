<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventorySales\Model\IsProductSalableCondition\BackOrderCondition as IsProductSalableBackOrderCondition;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;

/**
 * @inheritdoc
 */
class BackOrderCondition implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var IsProductSalableBackOrderCondition
     */
    private $backOrderCondition;

    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    /**
     * @param IsProductSalableBackOrderCondition $backOrderCondition
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     */
    public function __construct(
        IsProductSalableBackOrderCondition $backOrderCondition,
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory
    ) {
        $this->backOrderCondition = $backOrderCondition;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->productSalableResultFactory = $productSalableResultFactory;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId, float $requestedQty): ProductSalableResultInterface
    {
        $isValid = $this->backOrderCondition->execute($sku, $stockId);
        if (!$isValid) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'back_order-disabled',
                    'message' => __('Backorders are disabled')
                ])
            ];
            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }

        return $this->productSalableResultFactory->create(['errors' => []]);
    }
}
