<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\CatalogInventory\StockManagement;

use Magento\CatalogInventory\Model\StockManagement;
use Magento\Framework\Exception\LocalizedException;
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
 * Class provides around Plugin on \Magento\CatalogInventory\Model\StockManagement::revertProductsSale
 */
class ProcessRevertProductsSalePlugin
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
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->salesEventFactory = $salesEventFactory;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->websiteRepository = $websiteRepository;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
    }

    /**
     * @param StockManagement $subject
     * @param callable $proceed
     * @param float[] $items
     * @param int|null $websiteId
     * @return bool
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRevertProductsSale(
        StockManagement $subject,
        callable $proceed,
        $items,
        $websiteId = null
    ): bool {
        if (empty($items)) {
            return true;
        }

        if (null === $websiteId) {
            throw new LocalizedException(__('$websiteId parameter is required'));
        }

        $websiteCode = $this->websiteRepository->getById((int)$websiteId)->getCode();
        $salesChannel = $this->salesChannelFactory->create([
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $websiteCode
            ]
        ]);

        $salesEvent = $this->salesEventFactory->create([
            'type' => 'revert_products_sale',
            'objectType' => 'legacy_stock_management_api',
            'objectId' => 'none'
        ]);

        $productSkus = $this->getSkusByProductIds->execute(array_keys($items));
        $productTypes = $this->getProductTypesBySkus->execute(array_values($productSkus));

        $itemsToSell = [];
        foreach ($productSkus as $productId => $sku) {
            if (true === $this->isSourceItemManagementAllowedForProductType->execute($productTypes[$sku])) {
                $itemsToSell[] = $this->itemsToSellFactory->create([
                    'sku' => $sku,
                    'qty' => (float)$items[$productId]
                ]);
            }
        }

        $this->placeReservationsForSalesEvent->execute($itemsToSell, $salesChannel, $salesEvent);

        return true;
    }
}
