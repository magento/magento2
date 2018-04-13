<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\CatalogInventory\StockManagement;

use Magento\CatalogInventory\Api\RegisterProductSaleInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\RegisterSalesEventInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Class provides around Plugin on RegisterProductSaleInterface::registerProductsSale
 */
class ProcessRegisterProductsSalePlugin
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

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
     * @var RegisterSalesEventInterface
     */
    private $registerSalesEvent;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemToSellFactory;

    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        IsProductSalableForRequestedQtyInterface $isProductSalableForRequestedQty,
        RegisterSalesEventInterface $registerSalesEvent,
        SalesChannelInterfaceFactory $salesChannelFactory,
        WebsiteRepositoryInterface $websiteRepository,
        SalesEventInterfaceFactory $salesEventFactory,
        ItemToSellInterfaceFactory $itemToSellFactory
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->isProductSalableForRequestedQty = $isProductSalableForRequestedQty;
        $this->registerSalesEvent = $registerSalesEvent;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->websiteRepository = $websiteRepository;
        $this->salesEventFactory = $salesEventFactory;
        $this->itemToSellFactory = $itemToSellFactory;
    }

    /**
     * @param RegisterProductSaleInterface $subject
     * @param callable $proceed
     * @param float[] $items
     * @param int|null $websiteId
     * @return []
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRegisterProductsSale(
        RegisterProductSaleInterface $subject,
        callable $proceed,
        $items,
        $websiteId = null,
        $quoteId = null
    ) {
        if (empty($items)) {
            return [];
        }
        if (null === $websiteId) {
            throw new LocalizedException(__('$websiteId parameter is required'));
        }

        $salesEventType = SalesEventInterface::TYPE_ORDER_PLACED;
        $salesEventObjectType = SalesEventInterface::OBJECT_TYPE_QUOTE;
        $salesEventObjectId = $quoteId;
        if (null === $quoteId) {
            $salesEventType = 'none';
            $salesEventObjectType = 'none';
            $salesEventObjectId = 'none';
        }
        /** @var SalesEventInterface $salesEvent */
        $salesEvent = $this->salesEventFactory->create([
            'type' => $salesEventType,
            'objectType' => $salesEventObjectType,
            'objectId' => $salesEventObjectId
        ]);

        $productSkus = $this->getSkusByProductIds->execute(array_keys($items));
        /** @var ItemToSellInterface[] $itemsToSell */
        $itemsToSell = [];
        foreach ($productSkus as $productId => $sku) {
            $itemsToSell[] = $this->itemToSellFactory->create(['sku' => $sku, 'qty' => $items[$productId]]);
        }

        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        $salesChannel = $this->salesChannelFactory->create();
        $salesChannel->setCode($websiteCode);
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);

        $this->registerSalesEvent->execute($itemsToSell, $salesChannel, $salesEvent);

        return [];
    }
}
