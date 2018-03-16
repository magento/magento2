<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySales\Model\IsProductSalableCondition\ManageStockCondition as IsProductSalableManageStockCondition;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;

/**
 * @inheritdoc
 */
class ManageStockCondition implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var IsProductSalableManageStockCondition
     */
    private $manageStockCondition;

    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    /**
     * @param IsProductSalableManageStockCondition $manageStockCondition
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     */
    public function __construct(
        IsProductSalableManageStockCondition $manageStockCondition,
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory
    ) {
        $this->manageStockCondition = $manageStockCondition;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->productSalableResultFactory = $productSalableResultFactory;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId, float $requestedQty): ProductSalableResultInterface
    {
        $isSalable = $this->manageStockCondition->execute($sku, $stockId);
        if (!$isSalable) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'manage_stock-enabled',
                    'message' => __('Manage stock is enabled')
                ])
            ];
            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }
        return $this->productSalableResultFactory->create(['errors' => []]);
    }
}
