<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Observer\Stock;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\InventorySalesApi\Api\RegisterSalesEventInterface;
use Magento\CatalogInventory\Observer\ProductQty;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;

class PlaceReservationsOnQuoteSubmitSuccess implements ObserverInterface
{
    /**
     * @var RegisterSalesEventInterface
     */
    private $registerSalesEvent;

    /**
     * @var ProductQty
     */
    private $productQty;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @param RegisterSalesEventInterface $registerSalesEvent
     * @param ProductQty $productQty
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param SalesEventInterfaceFactory $salesEventFactory
     */
    public function __construct(
        RegisterSalesEventInterface $registerSalesEvent,
        ProductQty $productQty,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        WebsiteRepositoryInterface $websiteRepository,
        SalesChannelInterfaceFactory $salesChannelFactory,
        SalesEventInterfaceFactory $salesEventFactory
    ) {
        $this->registerSalesEvent = $registerSalesEvent;
        $this->productQty = $productQty;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->websiteRepository = $websiteRepository;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->salesEventFactory = $salesEventFactory;
    }

    /**
     * @param Observer $observer
     * return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $items = $this->productQty->getProductQty($quote->getAllItems());
        $websiteId = $quote->getStore()->getWebsiteId();

        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getEvent()->getOrder();

        $salesEventObjectType = SalesEventInterface::TYPE_ORDER;
        $salesEventObjectId = (string)$order->getEntityId();
        /** @var SalesEventInterface $salesEvent */
        $salesEvent = $this->salesEventFactory->create([
            'type' => $salesEventObjectType,
            'objectId' => $salesEventObjectId
        ]);

        $productSkus = $this->getSkusByProductIds->execute(array_keys($items));
        $itemsBySku = [];
        foreach ($productSkus as $productId => $sku) {
            $itemsBySku[$sku] = $items[$productId];
        }
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        $salesChannel = $this->salesChannelFactory->create();
        $salesChannel->setCode($websiteCode);
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);

        $this->registerSalesEvent->execute($itemsBySku, $salesChannel, $salesEvent);
        return;
    }
}