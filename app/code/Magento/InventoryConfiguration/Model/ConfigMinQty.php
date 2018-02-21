<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\Inventory\Model\GetStockItemDataInterface;
use Magento\InventoryCatalog\Model\GetLegacyStockItem;
use Magento\InventoryReservations\Model\GetReservationsQuantityInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Class ConfigMinQty
 */
class ConfigMinQty implements IsProductSalableInterface
{
    /**
     * @var GetLegacyStockItem
     */
    private $getLegacyStockItem;

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
     * ConfigMinQty constructor.
     * @param GetLegacyStockItem $getLegacyStockItem
     * @param Configuration $configuration
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     * @param GetStockItemDataInterface $getStockItemData
     */
    public function __construct(
        GetLegacyStockItem $getLegacyStockItem,
        Configuration $configuration,
        GetReservationsQuantityInterface $getReservationsQuantity,
        GetStockItemDataInterface $getStockItemData
    ) {
        $this->getLegacyStockItem = $getLegacyStockItem;
        $this->configuration = $configuration;
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return bool
     */
    public function execute(string $sku, int $stockId): bool
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        $legacyStockItem = $this->getLegacyStockItem->execute($sku);
        $qtyWithReservation = $stockItemData['quantity'] + $this->getReservationsQuantity->execute($sku, $stockId);
        $globalMinQty = $this->configuration->getMinQty();

        if (($legacyStockItem->getUseConfigMinQty() == 1 && $qtyWithReservation <= $globalMinQty)
            || ($legacyStockItem->getUseConfigMinQty() == 0 && $qtyWithReservation <= $legacyStockItem->getMinQty())
        ) {
            return false;
        }

        return true;
    }
}
