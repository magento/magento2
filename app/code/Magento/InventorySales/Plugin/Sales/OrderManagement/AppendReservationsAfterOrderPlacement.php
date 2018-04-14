<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\Sales\OrderManagement;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\InventorySalesApi\Api\RegisterSalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

class AppendReservationsAfterOrderPlacement
{
    /**
     * @var RegisterSalesEventInterface
     */
    private $registerSalesEvent;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param RegisterSalesEventInterface $registerSalesEvent
     * @param ProductQty $productQty
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RegisterSalesEventInterface $registerSalesEvent,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        WebsiteRepositoryInterface $websiteRepository,
        SalesChannelInterfaceFactory $salesChannelFactory,
        SalesEventInterfaceFactory $salesEventFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->registerSalesEvent = $registerSalesEvent;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->websiteRepository = $websiteRepository;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->salesEventFactory = $salesEventFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterPlace(OrderManagementInterface $subject, OrderInterface $order) : OrderInterface
    {
        $salesEventObjectType = SalesEventInterface::TYPE_ORDER;
        $salesEventObjectId = (string)$order->getEntityId();
        /** @var SalesEventInterface $salesEvent */
        $salesEvent = $this->salesEventFactory->create([
            'type' => $salesEventObjectType,
            'objectId' => $salesEventObjectId
        ]);

        $itemsById = [];
        /** @var OrderItemInterface $item **/
        foreach ($order->getItems() as $item) {
            $itemsById[$item->getProductId()] = $item->getQtyOrdered();
        }
        $productSkus = $this->getSkusByProductIds->execute(array_keys($itemsById));
        $itemsBySku = [];
        foreach ($productSkus as $productId => $sku) {
            $itemsBySku[$sku] = $itemsById[$productId];
        }

        $websiteId = $this->storeManager->getStore($order->getStoreId())->getWebsiteId();
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        $salesChannel = $this->salesChannelFactory->create();
        $salesChannel->setCode($websiteCode);
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);

        $this->registerSalesEvent->execute($itemsBySku, $salesChannel, $salesEvent);
        return $order;
    }
}
