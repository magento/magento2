<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\CatalogInventory\StockManagement;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalog\Model\GetProductTypesBySkus;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventoryConfiguration\Model\GetAllowedProductTypesForSourceItemsInterface;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;

/**
 * Class provides around Plugin on \Magento\CatalogInventory\Model\StockManagement::registerProductsSale
 */
class ProcessRegisterProductsSalePlugin
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    /**
     * @var IsProductSalableForRequestedQtyInterface
     */
    private $isProductSalableForRequestedQty;

    /**
     * @var GetAllowedProductTypesForSourceItemsInterface
     */
    private $allowedProductTypesForSourceItems;

    /**
     * @var GetProductTypesBySkus
     */
    private $getProductTypesBySkus;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     * @param IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty
     * @param GetAllowedProductTypesForSourceItemsInterface $allowedProductTypesForSourceItems
     * @param GetProductTypesBySkus $getProductTypesBySkus
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        GetAllowedProductTypesForSourceItemsInterface $allowedProductTypesForSourceItems,
        GetProductTypesBySkus $getProductTypesBySkus

    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->allowedProductTypesForSourceItems = $allowedProductTypesForSourceItems;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
    }

    /**
     * @param StockManagement $subject
     * @param callable $proceed
     * @param float[] $items
     * @param int|null $websiteId
     * @return StockItemInterface[]
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRegisterProductsSale(StockManagement $subject, callable $proceed, $items, $websiteId = null)
    {
        if (empty($items)) {
            return [];
        }
        $productSkus = $this->getSkusByProductIds->execute(array_keys($items));
        $excludedItems = $this->getExcludedItems($items, $productSkus);
        if (!empty($excludedItems)) {
            $proceed($excludedItems, $websiteId);
        }
        if (null === $websiteId) {
            //TODO: Do we need to throw exception?
            throw new LocalizedException(__('$websiteId parameter is required'));
        }
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();
        $productSkus = $this->getSkusByProductIds->execute(array_keys($items));
        $this->checkItemsQuantity($items, $productSkus, $stockId);
        $reservations = [];
        foreach ($productSkus as $productId => $sku) {
            $reservations[] = $this->reservationBuilder
                ->setSku($sku)
                ->setQuantity(-(float)$items[$productId])
                ->setStockId($stockId)
                ->build();
        }
        if (!empty($reservations)) {
            $this->appendReservations->execute($reservations);
        }

        return [];
    }

    /**
     * Check is all items salable
     *
     * @param array $items
     * @param array $productSkus
     * @param int $stockId
     * @return void
     * @throws LocalizedException
     */
    private function checkItemsQuantity(array $items, array $productSkus, int $stockId)
    {
        foreach ($productSkus as $productId => $sku) {
            $qty = (float)$items[$productId];
            $isSalable = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $qty)->isSalable();
            if (!$isSalable) {
                throw new LocalizedException(
                    __('Not all of your products are available in the requested quantity.')
                );
            }
        }
    }

    /**
     * Build list of items connected to products with unsupported types.
     *
     * Remove items connected to products with unsupported product types and put them into separate array,
     * in order to process them with standard behavior.
     *
     * @param array $items
     * @param array $productSkus
     * @return array
     */
    private function getExcludedItems(array &$items, array &$productSkus): array
    {
        $excludedItems = [];
        $incomingProductTypes = $this->getProductTypesBySkus->execute($productSkus);
        $allowedProductTypes = $this->allowedProductTypesForSourceItems->execute();
        foreach ($incomingProductTypes as $sku => $type) {
            if (!in_array($type, $allowedProductTypes, true)) {
                $excludedProductId = array_search($sku, $productSkus, true);
                $excludedItems[$excludedProductId] = $items[$excludedProductId];
                //Exclude unsupported product types from new behavior process.
                unset($productSkus[$excludedProductId], $items[$excludedProductId]);
            }
        }

        return $excludedItems;
    }
}
