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
use Magento\InventorySalesApi\Model\ReturnProcessor\ProcessRefundItemsInterface;
use Magento\InventorySalesApi\Model\ReturnProcessor\GetSourceDeductedOrderItemsInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\InventorySourceDeductionApi\Model\ItemToDeductFactory;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestFactory;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionService;

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
     * @var ItemToDeductFactory
     */
    private $itemToDeductFactory;

    /**
     * @var SourceDeductionRequestFactory
     */
    private $sourceDeductionRequestFactory;

    /**
     * @var SourceDeductionService
     */
    private $sourceDeductionService;

    /**
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param GetSourceDeductedOrderItemsInterface $getSourceDeductedOrderItems
     * @param ItemToDeductFactory $itemToDeductFactory
     * @param SourceDeductionRequestFactory $sourceDeductionRequestFactory
     * @param SourceDeductionService $sourceDeductionService
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        SalesChannelInterfaceFactory $salesChannelFactory,
        SalesEventInterfaceFactory $salesEventFactory,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        GetSourceDeductedOrderItemsInterface $getSourceDeductedOrderItems,
        ItemToDeductFactory $itemToDeductFactory,
        SourceDeductionRequestFactory $sourceDeductionRequestFactory,
        SourceDeductionService $sourceDeductionService
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->salesEventFactory = $salesEventFactory;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->getSourceDeductedOrderItems = $getSourceDeductedOrderItems;
        $this->itemToDeductFactory = $itemToDeductFactory;
        $this->sourceDeductionRequestFactory = $sourceDeductionRequestFactory;
        $this->sourceDeductionService = $sourceDeductionService;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(
        OrderInterface $order,
        array $itemsToRefund,
        array $returnToStockItems
    ) {
        $salesChannel = $this->getSalesChannelForOrder($order);
        $deductedItems = $this->getSourceDeductedOrderItems->execute($order, $returnToStockItems);
        $itemToSell = $backItemsPerSource = [];

        foreach ($itemsToRefund as $item) {
            $sku = $item->getSku();

            $totalDeductedQty = $this->getTotalDeductedQty($item, $deductedItems);
            $processedQty = $item->getProcessedQuantity() - $totalDeductedQty;
            $qtyBackToSource = ($processedQty > 0) ? $item->getQuantity() - $processedQty : $item->getQuantity();
            $qtyBackToStock = ($qtyBackToSource > 0) ? $item->getQuantity() - $qtyBackToSource : $item->getQuantity();

            if ($qtyBackToStock > 0) {
                $itemToSell[] = $this->itemsToSellFactory->create([
                    'sku' => $sku,
                    'qty' => (float)$qtyBackToStock
                ]);
            }

            foreach ($deductedItems as $deductedItemResult) {
                $sourceCode = $deductedItemResult->getSourceCode();
                foreach ($deductedItemResult->getItems() as $deductedItem) {
                    if ($sku != $deductedItem->getSku() || $this->isZero((float)$qtyBackToSource)) {
                        continue;
                    }

                    $backQty = min($deductedItem->getQuantity(), $qtyBackToSource);

                    $backItemsPerSource[$sourceCode][] = $this->itemToDeductFactory->create([
                        'sku' => $deductedItem->getSku(),
                        'qty' => -$backQty
                    ]);
                    $qtyBackToSource -= $backQty;
                }
            }
        }

        /** @var SalesEventInterface $salesEvent */
        $salesEvent = $this->salesEventFactory->create([
            'type' => SalesEventInterface::EVENT_CREDITMEMO_CREATED,
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => (string)$order->getEntityId()
        ]);

        foreach ($backItemsPerSource as $sourceCode => $items) {
            $sourceDeductionRequest = $this->sourceDeductionRequestFactory->create([
                'sourceCode' => $sourceCode,
                'items' => $items,
                'salesChannel' => $salesChannel,
                'salesEvent' => $salesEvent
            ]);
            $this->sourceDeductionService->execute($sourceDeductionRequest);
        }

        $this->placeReservationsForSalesEvent->execute($itemToSell, $salesChannel, $salesEvent);
    }

    /**
     * @param $item
     * @param array $deductedItems
     * @return float
     */
    private function getTotalDeductedQty($item, array $deductedItems): float
    {
        $result = 0;

        foreach ($deductedItems as $deductedItemResult) {
            foreach ($deductedItemResult->getItems() as $deductedItem) {
                if ($item->getSku() != $deductedItem->getSku()) {
                    continue;
                }
                $result += $deductedItem->getQuantity();
            }
        }

        return $result;
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
