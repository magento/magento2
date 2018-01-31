<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\Stock\Item as LegacyStockItem;
use Magento\CatalogInventory\Model\Stock\StockItemRepository as LegacyStockItemRepository;
use Magento\InventoryApi\Api\IsProductInStockInterface;

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
     * @var ProductResourceModel
     */
    private $productResource;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @param GetStockItemDataInterface $getStockItemData
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     * @param Configuration $configuration
     * @param LegacyStockItemRepository $legacyStockItemRepository
     * @param ProductResourceModel $productResource
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     */
    public function __construct(
        GetStockItemDataInterface $getStockItemData,
        GetReservationsQuantityInterface $getReservationsQuantity,
        Configuration $configuration,
        LegacyStockItemRepository $legacyStockItemRepository,
        ProductResourceModel $productResource,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
    ) {
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->configuration = $configuration;
        $this->legacyStockItemRepository = $legacyStockItemRepository;
        $this->productResource = $productResource;
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

        if ($this->isManageStock($legacyStockItem)) {
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
    private function isManageStock(LegacyStockItem $legacyStockItem): bool
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
        $productIds = $this->productResource->getProductsIdsBySkus([$sku]);
        $searchCriteria = $this->stockItemCriteriaFactory->create();
        $searchCriteria->addFilter(StockItemInterface::PRODUCT_ID, StockItemInterface::PRODUCT_ID, $productIds[$sku]);

        $legacyStockItem = $this->legacyStockItemRepository->getList($searchCriteria);
        $items = $legacyStockItem->getItems();

        return reset($items);
    }
}
