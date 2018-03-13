<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventoryReservations\Model\GetReservationsQuantityInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySales\Model\GetStockItemDataInterface;

/**
 * @inheritdoc
 */
class IsSalableWithReservationsCondition implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $getReservationsQuantity;

    /**
     * @var ProductSalabilityErrorFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var IsProductSalableResultFactory
     */
    private $isProductSalableResultFactory;

    /**
     * @param GetStockItemDataInterface $getStockItemData
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     */
    public function __construct(
        GetStockItemDataInterface $getStockItemData,
        GetReservationsQuantityInterface $getReservationsQuantity,
        ProductSalabilityErrorFactory $productSalabilityErrorFactory,
        IsProductSalableResultFactory $isProductSalableResultFactory
    ) {
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->isProductSalableResultFactory = $isProductSalableResultFactory;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId, float $requestedQty): IsProductSalableResultInterface
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        if (null === $stockItemData) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_salable_with_reservations-no_data',
                    'message' => __('The requested sku is not assigned to given stock')
                ])
            ];
            return $this->isProductSalableResultFactory->create(['errors' => $errors]);
        }

        $qtyWithReservation = $stockItemData['quantity'] + $this->getReservationsQuantity->execute($sku, $stockId);
        $isEnoughQty = (bool)$stockItemData['is_salable'] && $qtyWithReservation >= $requestedQty;
        if (!$isEnoughQty) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_salable_with_reservations-not_enough_qty',
                    'message' => __('The requested qty is not available')
                ])
            ];
            return $this->isProductSalableResultFactory->create(['errors' => $errors]);
        }
        return $this->isProductSalableResultFactory->create(['errors' => []]);
    }
}
