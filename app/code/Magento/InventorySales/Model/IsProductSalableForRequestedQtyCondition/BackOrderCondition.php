<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventorySales\Model\IsProductSalableCondition\BackOrderCondition as IsProductSalableBackOrderCondition;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;

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
     * @var ProductSalabilityErrorFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var IsProductSalableResultFactory
     */
    private $isProductSalableResultFactory;

    public function __construct(
        IsProductSalableBackOrderCondition $backOrderCondition,
        ProductSalabilityErrorFactory $productSalabilityErrorFactory,
        IsProductSalableResultFactory $isProductSalableResultFactory
    ) {
        $this->backOrderCondition = $backOrderCondition;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->isProductSalableResultFactory = $isProductSalableResultFactory;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId, float $requestedQty): IsProductSalableResultInterface
    {
        $isValid = $this->backOrderCondition->execute($sku, $stockId);
        if (!$isValid) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'back_order-disabled',
                    'message' => __('Backorders are disabled')
                ])
            ];
            return $this->isProductSalableResultFactory->create(['errors' => $errors]);
        }

        return $this->isProductSalableResultFactory->create(['errors' => []]);
    }
}
