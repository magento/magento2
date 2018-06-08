<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventorySalesApi\Model\ReturnProcessor\Request\ItemsToRefundInterface;
use Magento\InventorySalesApi\Model\ReturnProcessor\ProcessRefundItemsInterface;
use Magento\InventorySalesApi\Model\ReturnProcessor\GetSourceDeductedOrderItemsInterface;
use Magento\InventorySales\Model\ReturnProcessor\Request\BackItemQtyRequestFactory;
use Magento\InventorySales\Model\ReturnProcessor\ProcessBackItemQtyToSource;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

class ProcessRefundItems implements ProcessRefundItemsInterface
{
    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var ProcessBackItemQtyToSource
     */
    private $processBackItemQtyToSource;

    /**
     * @var BackItemQtyRequestFactory
     */
    private $backItemQtyRequestFactory;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemsToSellFactory;

    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

    /**
     * @var GetSourceDeductedOrderItemsInterface
     */
    private $getSourceDeductedOrderItems;

    /**
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param BackItemQtyRequestFactory $backItemQtyRequestFactory
     * @param ProcessBackItemQtyToSource $processBackItemQtyToSource
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param GetSourceDeductedOrderItemsInterface $getSourceDeductedOrderItems
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        SalesChannelInterfaceFactory $salesChannelFactory,
        BackItemQtyRequestFactory $backItemQtyRequestFactory,
        ProcessBackItemQtyToSource $processBackItemQtyToSource,
        SalesEventInterfaceFactory $salesEventFactory,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        GetSourceDeductedOrderItemsInterface $getSourceDeductedOrderItems
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->backItemQtyRequestFactory = $backItemQtyRequestFactory;
        $this->processBackItemQtyToSource = $processBackItemQtyToSource;
        $this->salesEventFactory = $salesEventFactory;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->getSourceDeductedOrderItems = $getSourceDeductedOrderItems;
    }

    /**
     * @inheritdoc
     */
    public function execute(
        OrderInterface $order,
        array $itemsToRefund,
        array $returnToStockItems
    ) {
        $salesChannel = $this->getSalesChannelForOrder($order);
        $deductedItems = $this->getSourceDeductedOrderItems->execute($order, $returnToStockItems);
        $itemToSell = [];

        /** @var ItemsToRefundInterface $item */
        foreach ($itemsToRefund as $item) {
            $sku = $item->getSku();
            $qtyBackToSource = $item->getQuantity();
            $originalProcessedQty = $item->getProcessedQuantity() + $item->getQuantity();

            foreach ($deductedItems as $deductedItemResult) {
                $sourceCode = $deductedItemResult->getSourceCode();
                foreach ($deductedItemResult->getItems() as $deductedItem) {
                    if ($sku != $deductedItem->getSku()) {
                        continue;
                    }
                    $availableQtyToBack = $deductedItem->getQuantity() + $originalProcessedQty;
                    $backQty = min($availableQtyToBack, $qtyBackToSource);
                    $originalProcessedQty += $deductedItem->getQuantity();

                    if ($this->isZero((float)$availableQtyToBack)) {
                        continue;
                    }

                    $backItemQtyRequest = $this->backItemQtyRequestFactory->create([
                        'sourceCode' => $sourceCode,
                        'sku' => $deductedItem->getSku(),
                        'qty' => $backQty
                    ]);
                    $this->processBackItemQtyToSource->execute($backItemQtyRequest, $salesChannel);
                    $qtyBackToSource -= $backQty;
                }
            }

            if ($qtyBackToSource > 0) {
                $itemToSell[] = $this->itemsToSellFactory->create([
                    'sku' => $sku,
                    'qty' => (float)$qtyBackToSource
                ]);
            }
        }

        /** @var SalesEventInterface $salesEvent */
        $salesEvent = $this->salesEventFactory->create([
            'type' => SalesEventInterface::EVENT_CREDITMEMO_CREATED,
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => (string)$order->getEntityId()
        ]);

        $this->placeReservationsForSalesEvent->execute($itemToSell, $salesChannel, $salesEvent);
    }

    /**
     * @param OrderInterface $order
     * @return SalesChannelInterface
     */
    private function getSalesChannelForOrder(OrderInterface $order): SalesChannelInterface
    {
        $websiteId = (int)$order->getStore()->getWebsiteId();
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();

        return $this->salesChannelFactory->create([
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $websiteCode
            ]
        ]);
    }

    /**
     * Compare float number with some epsilon
     *
     * @param float $floatNumber
     *
     * @return bool
     */
    private function isZero(float $floatNumber): bool
    {
        return $floatNumber < 0.0000001;
    }
}
