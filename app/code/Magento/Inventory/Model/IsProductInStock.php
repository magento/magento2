<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Stock\StockItemRepository as LegacyStockItemRepository;
use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkus;
use Magento\CatalogInventory\Model\Stock\Item as LegacyStockItem;

/**
 * Return product availability by Product SKU and Stock Id (stock data + reservations)
 */
class IsProductInStock implements IsProductInStockInterface
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
     * @var Configuration
     */
    private $configuration;

    /**
     * @var LegacyStockItemRepository
     */
    private $legacyStockItemRepository;

    /**
     * @var GetProductIdsBySkus
     */
    private $getProductIdsBySkus;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @param GetStockItemDataInterface $getStockItemData
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     * @param Configuration $configuration
     * @param LegacyStockItemRepository $legacyStockItemRepository
     * @param GetProductIdsBySkus $getProductIdsBySkus
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     */
    public function __construct(
        GetStockItemDataInterface $getStockItemData,
        GetReservationsQuantityInterface $getReservationsQuantity,
        Configuration $configuration,
        LegacyStockItemRepository $legacyStockItemRepository,
        GetProductIdsBySkus $getProductIdsBySkus,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
    ) {
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->configuration = $configuration;
        $this->legacyStockItemRepository = $legacyStockItemRepository;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        $isInStock = (bool)$stockItemData['is_salable'];
        $qtyWithReservation = $stockItemData['quantity'] + $this->getReservationsQuantity->execute($sku, $stockId);
        $globalMinQty = $this->configuration->getMinQty();
        $legacyStockItem = $this->getLegacyStockItem($sku);

        if ($this->getManageStock($legacyStockItem)) {
            if (($legacyStockItem->getUseConfigMinQty() == 1 && $qtyWithReservation <= $globalMinQty)
                || ($legacyStockItem->getUseConfigMinQty() == 0 && $qtyWithReservation <= $legacyStockItem->getMinQty())
            ) {
                $isInStock = false;
            }
        }

        return $isInStock;
    }

    /**
     * @param LegacyStockItem $legacyStockItem
     *
     * @return bool
     */
    private function getManageStock(LegacyStockItem $legacyStockItem): bool
    {
        $globalManageStock = $this->configuration->getManageStock();
        $manageStock = false;
        if (($legacyStockItem->getUseConfigManageStock() == 1 && $globalManageStock == 1)
            || ($legacyStockItem->getUseConfigManageStock() == 0 && $legacyStockItem->getManageStock() == 1)
        ) {
            $manageStock = true;
        }

        return $manageStock;
    }

    /**
     * @param string $sku
     *
     * @return LegacyStockItem
     */
    private function getLegacyStockItem(string $sku): LegacyStockItem
    {
        $productIds = $this->getProductIdsBySkus->execute([$sku]);
        $searchCriteria = $this->stockItemCriteriaFactory->create();
        $searchCriteria->addFilter(StockItemInterface::PRODUCT_ID, StockItemInterface::PRODUCT_ID, $productIds[$sku]);

        $legacyStockItem = $this->legacyStockItemRepository->getList($searchCriteria);
        $items = $legacyStockItem->getItems();

        return reset($items);
    }
}
