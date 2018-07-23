<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;

/**
 * @inheritdoc
 */
class BackOrderNotifyCustomerCondition implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param GetStockItemDataInterface $getStockItemData
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     */
    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetStockItemDataInterface $getStockItemData,
        ProductSalableResultInterfaceFactory $productSalableResultFactory,
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getStockItemData = $getStockItemData;
        $this->productSalableResultFactory = $productSalableResultFactory;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId, float $requestedQty): ProductSalableResultInterface
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);

        if ($stockItemConfiguration->getBackorders() === StockItemConfigurationInterface::BACKORDERS_YES_NOTIFY) {
            $stockItemData = $this->getStockItemData->execute($sku, $stockId);
            if (null === $stockItemData) {
                return $this->productSalableResultFactory->create(['errors' => []]);
            }

            $backOrderQty = $requestedQty - $stockItemData[GetStockItemDataInterface::QUANTITY];
            if ($backOrderQty > 0) {
                $errors = [
                    $this->productSalabilityErrorFactory->create([
                            'code' => 'back_order-not-enough',
                            'message' => __(
                                'We don\'t have as many quantity as you requested, '
                                . 'but we\'ll back order the remaining %1.',
                                $backOrderQty * 1
                            )])
                ];
                return $this->productSalableResultFactory->create(['errors' => $errors]);
            }
        }

        return $this->productSalableResultFactory->create(['errors' => []]);
    }
}
