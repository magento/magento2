<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition;

use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\Math\Division as MathDivision;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryReservations\Model\GetReservationsQuantityInterface;
use Magento\InventorySales\Model\GetStockItemDataInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;

/**
 * @inheritdoc
 */
class IsCorrectQtyCondition implements IsProductSalableForRequestedQtyInterface
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $getReservationsQuantity;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var MathDivision
     */
    protected $mathDivision;

    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        Configuration $configuration,
        GetReservationsQuantityInterface $getReservationsQuantity,
        GetStockItemDataInterface $getStockItemData,
        MathDivision $mathDivision
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->configuration = $configuration;
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->mathDivision = $mathDivision;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId, float $requestedQty): bool
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        if (null === $stockItemConfiguration) {
            return false;
        }

        $stockItemData = $this->getStockItemData->execute($sku, $stockId);

        $qtyWithReservation = $stockItemData['quantity'] + $this->getReservationsQuantity->execute($sku, $stockId);
        if ($requestedQty > $qtyWithReservation) {
            return false;
        }

        $globalMinQty = $this->configuration->getMinQty();
        if (($stockItemConfiguration->isUseConfigMinQty() == 1 && $requestedQty < $globalMinQty)
            || ($stockItemConfiguration->isUseConfigMinQty() == 0
                && $requestedQty < $stockItemConfiguration->getMinQty()
            )) {
            return false;
        }

        $globalMaxSaleQty = $this->configuration->getMaxSaleQty();
        if (($stockItemConfiguration->isUseConfigMaxSaleQty() == 1 && $requestedQty > $globalMaxSaleQty)
            || ($stockItemConfiguration->isUseConfigMaxSaleQty() == 0
                && $requestedQty > $stockItemConfiguration->getMaxSaleQty()
            )) {
            return false;
        }

        $globalEnableQtyIncrements = $this->configuration->getEnableQtyIncrements();
        if (($globalEnableQtyIncrements
                && $this->mathDivision->getExactDivision($requestedQty, $this->configuration->getQtyIncrements()))
            || ($stockItemConfiguration->isEnableQtyIncrements()
                && $this->mathDivision->getExactDivision($requestedQty, $stockItemConfiguration->getQtyIncrements()))) {
            return false;
        }

        return true;
    }
}