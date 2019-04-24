<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SalableQuantityInconsistency;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Model\IsProductAssignedToStockInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException;
use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency;

/**
 * Remove all reservations with incomplete state
 */
class FilterManagedStockProducts
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var IsProductAssignedToStockInterface
     */
    private $isProductAssignedToStock;

    /**
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param IsProductAssignedToStockInterface $isProductAssignedToStock
     */
    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        IsProductAssignedToStockInterface $isProductAssignedToStock
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->isProductAssignedToStock = $isProductAssignedToStock;
    }

    /**
     * Remove all reservations with incomplete state
     *
     * @param SalableQuantityInconsistency[] $inconsistencies
     * @return SalableQuantityInconsistency[]
     * @throws LocalizedException
     * @throws SkuIsNotAssignedToStockException
     */
    public function execute(array $inconsistencies): array
    {
        foreach ($inconsistencies as $inconsistency) {
            $filteredItems = [];
            foreach ($inconsistency->getItems() as $sku => $qty) {
                if (false === $this->isProductAssignedToStock->execute($sku, $inconsistency->getStockId())) {
                    continue;
                }

                $stockConfiguration = $this->getStockItemConfiguration->execute($sku, $inconsistency->getStockId());
                if ($stockConfiguration->isManageStock()) {
                    $filteredItems[$sku] = $qty;
                }
            }
            $inconsistency->setItems($filteredItems);
        }

        return $inconsistencies;
    }
}
