<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\CatalogInventory\StockManagement;

use Magento\CatalogInventory\Model\StockManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Class provides around Plugin on \Magento\CatalogInventory\Model\StockManagement::backItemQty
 */
class ProcessBackItemQtyPlugin
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemsToSellFactory;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        SalesEventInterfaceFactory $salesEventFactory,
        SalesChannelInterfaceFactory $salesChannelFactory,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        WebsiteRepositoryInterface $websiteRepository,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        GetProductTypesBySkusInterface $getProductTypesBySkus
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->salesEventFactory = $salesEventFactory;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->websiteRepository = $websiteRepository;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
    }

    /**
     * @param StockManagement $subject
     * @param callable $proceed
     * @param int $productId
     * @param float $qty
     * @param int|null $scopeId
     * @return bool
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBackItemQty(
        StockManagement $subject,
        callable $proceed,
        $productId,
        $qty,
        $scopeId = null
    ): bool {
        if (null === $scopeId) {
            throw new LocalizedException(__('$scopeId is required'));
        }

        try {
            $productSku = $this->getSkusByProductIds->execute([$productId])[$productId];
        } catch (NoSuchEntityException $e) {
            /**
             * As it was decided the Inventory should not use data constraints depending on Catalog
             * (these two systems are not highly coupled, i.e. Magento does not sync data between them, so that
             * it's possible that SKU exists in Catalog, but does not exist in Inventory and vice versa)
             * it is necessary for Magento to have an ability to process placed orders even with
             * deleted or non-existing products
             */
            return true;
        }
        $productType = $this->getProductTypesBySkus->execute([$productSku])[$productSku];

        if (true === $this->isSourceItemManagementAllowedForProductType->execute($productType)) {
            $websiteCode = $this->websiteRepository->getById((int)$scopeId)->getCode();
            $salesChannel = $this->salesChannelFactory->create([
                'data' => [
                    'type' => SalesChannelInterface::TYPE_WEBSITE,
                    'code' => $websiteCode
                ]
            ]);

            $salesEvent = $this->salesEventFactory->create([
                'type' => 'back_item_qty',
                'objectType' => 'legacy_stock_management_api',
                'objectId' => 'none'
            ]);

            $itemToSell = $this->itemsToSellFactory->create([
                'sku' => $productSku,
                'qty' => (float)$qty
            ]);

            $this->placeReservationsForSalesEvent->execute([$itemToSell], $salesChannel, $salesEvent);
        }

        return true;
    }
}
