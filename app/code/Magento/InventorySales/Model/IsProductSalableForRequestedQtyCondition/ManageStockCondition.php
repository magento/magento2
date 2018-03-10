<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;

/**
 * @inheritdoc
 */
class ManageStockCondition implements IsProductSalableForRequestedQtyInterface
{
    /** @var \Magento\InventorySales\Model\IsProductSalableCondition\ManageStockCondition */
    private $manageStockCondition;

    /**
     * @var ProductSalabilityErrorFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var IsProductSalableResultFactory
     */
    private $isProductSalableResultFactory;

    /**
     * ManageStockCondition constructor.
     * @param \Magento\InventorySales\Model\IsProductSalableCondition\ManageStockCondition $manageStockCondition
     */
    public function __construct(
        \Magento\InventorySales\Model\IsProductSalableCondition\ManageStockCondition $manageStockCondition,
        ProductSalabilityErrorFactory $productSalabilityErrorFactory,
        IsProductSalableResultFactory $isProductSalableResultFactory
    ) {
        $this->manageStockCondition = $manageStockCondition;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->isProductSalableResultFactory = $isProductSalableResultFactory;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId, float $requestedQty): IsProductSalableResultInterface
    {
        $isSalable = $this->manageStockCondition->execute($sku, $stockId);
        if (!$isSalable) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'manage_stock-enabled',
                    'message' => __('Manage stock is enabled')
                ])
            ];
            return $this->isProductSalableResultFactory->create(['errors' => $errors]);
        }
        return $this->isProductSalableResultFactory->create(['errors' => []]);
    }
}
