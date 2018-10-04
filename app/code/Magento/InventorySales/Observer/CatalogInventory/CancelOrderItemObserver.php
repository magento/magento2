<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Observer\CatalogInventory;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Api\WebsiteRepositoryInterface;

class CancelOrderItemObserver implements ObserverInterface
{
    /**
     * @var Processor
     */
    private $priceIndexer;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

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
     * @param Processor $priceIndexer
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        Processor $priceIndexer,
        SalesEventInterfaceFactory $salesEventFactory,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        SalesChannelInterfaceFactory $salesChannelFactory,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->priceIndexer = $priceIndexer;
        $this->salesEventFactory = $salesEventFactory;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var OrderItem $item */
        $item = $observer->getEvent()->getItem();
        $qty = $item->getQtyToCancel();
        if ($this->canCancelOrderItem($item) && $qty) {
            try {
                $productSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                    [$item->getProductId()]
                )[$item->getProductId()];
            } catch (NoSuchEntityException $e) {
                /**
                 * As it was decided the Inventory should not use data constraints depending on Catalog
                 * (these two systems are not highly coupled, i.e. Magento does not sync data between them, so that
                 * it's possible that SKU exists in Catalog, but does not exist in Inventory and vice versa)
                 * it is necessary for Magento to have an ability to process placed orders even with
                 * deleted or non-existing products
                 */
                return;
            }

            $itemToSell = $this->itemsToSellFactory->create([
                'sku' => $productSku,
                'qty' => (float)$qty
            ]);

            $websiteId = $item->getStore()->getWebsiteId();
            $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
            $salesChannel = $this->salesChannelFactory->create([
                'data' => [
                    'type' => SalesChannelInterface::TYPE_WEBSITE,
                    'code' => $websiteCode
                ]
            ]);

            /** @var SalesEventInterface $salesEvent */
            $salesEvent = $this->salesEventFactory->create([
                'type' => SalesEventInterface::EVENT_ORDER_CANCELED,
                'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
                'objectId' => (string)$item->getOrderId()
            ]);

            $this->placeReservationsForSalesEvent->execute([$itemToSell], $salesChannel, $salesEvent);
        }

        $this->priceIndexer->reindexRow($item->getProductId());
    }

    /**
     * @param OrderItem $orderItem
     * @return bool
     */
    private function canCancelOrderItem(OrderItem $orderItem): bool
    {
        if ($orderItem->getId() && $orderItem->getProductId() && !$orderItem->isDummy()) {
            return true;
        }
        return false;
    }
}
