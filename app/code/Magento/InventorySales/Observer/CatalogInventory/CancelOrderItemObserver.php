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
use Magento\InventorySales\Model\GetItemsToCancelFromOrderItem;
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
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var GetItemsToCancelFromOrderItem
     */
    private $getItemsToCancelFromOrderItem;

    /**
     * @param Processor $priceIndexer
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GetItemsToCancelFromOrderItem $getItemsToCancelFromOrderItem
     */
    public function __construct(
        Processor $priceIndexer,
        SalesEventInterfaceFactory $salesEventFactory,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        SalesChannelInterfaceFactory $salesChannelFactory,
        WebsiteRepositoryInterface $websiteRepository,
        GetItemsToCancelFromOrderItem $getItemsToCancelFromOrderItem
    ) {
        $this->priceIndexer = $priceIndexer;
        $this->salesEventFactory = $salesEventFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->websiteRepository = $websiteRepository;
        $this->getItemsToCancelFromOrderItem = $getItemsToCancelFromOrderItem;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer): void
    {
        /** @var OrderItem $item */
        $orderItem = $observer->getEvent()->getItem();

        $itemsToCancel = $this->getItemsToCancelFromOrderItem->execute($orderItem);

        if (empty($itemsToCancel)) {
            return;
        }

        $websiteId = $orderItem->getStore()->getWebsiteId();
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        $salesChannel = $this->salesChannelFactory->create([
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $websiteCode
            ]
        ]);

        $salesEvent = $this->salesEventFactory->create([
            'type' => SalesEventInterface::EVENT_ORDER_CANCELED,
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => (string)$orderItem->getOrderId()
        ]);

        $this->placeReservationsForSalesEvent->execute($itemsToCancel, $salesChannel, $salesEvent);

        $this->priceIndexer->reindexRow($orderItem->getProductId());
    }
}
