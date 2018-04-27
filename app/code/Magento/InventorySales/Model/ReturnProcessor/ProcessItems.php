<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventoryShipping\Model\GetSourceItemBySourceCodeAndSku;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\InventorySales\Model\ReturnProcessor\GetShippedItemsPerSourceByPriority;

class ProcessItems
{
    /**
     * @var GetSourceItemBySourceCodeAndSku
     */
    private $getSourceItemBySourceCodeAndSku;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var GetShippedItemsPerSourceByPriority
     */
    private $getShippedItemsPerSourceByPriority;

    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

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
     * @param GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param GetShippedItemsPerSourceByPriority $getShippedItemsPerSourceByPriority
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     */
    public function __construct(
        GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku,
        SourceItemsSaveInterface $sourceItemsSave,
        GetShippedItemsPerSourceByPriority $getShippedItemsPerSourceByPriority,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        WebsiteRepositoryInterface $websiteRepository,
        SalesChannelInterfaceFactory $salesChannelFactory,
        SalesEventInterfaceFactory $salesEventFactory,
        ItemToSellInterfaceFactory $itemsToSellFactory
    ) {
        $this->getSourceItemBySourceCodeAndSku = $getSourceItemBySourceCodeAndSku;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->getShippedItemsPerSourceByPriority = $getShippedItemsPerSourceByPriority;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->websiteRepository = $websiteRepository;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->salesEventFactory = $salesEventFactory;
        $this->itemsToSellFactory = $itemsToSellFactory;
    }

    /**
     * @param OrderInterface $order
     * @param array $itemsToRefund
     * @param array $processedItems
     * @param array $returnToStockItems
     */
    public function execute(
        OrderInterface $order,
        array $itemsToRefund,
        array $processedItems,
        array $returnToStockItems
    ) {
        $sourceItemToSave = [];
        $shippedItems = $this->getShippedItemsPerSourceByPriority->execute($order, $returnToStockItems);
        foreach ($itemsToRefund as $sku => $qty) {
            if (empty($processedItems[$sku])) {
                continue;
            }

            $qtyBackToSource = $qty;
            $originalProcessedQty = $processedItems[$sku] + $qty;

            foreach ($shippedItems as $sourceCode => $data) {
                if (empty($data[$sku])) {
                    continue;
                }

                $availableQtyToBack = $data[$sku] + $originalProcessedQty;
                $backQty = min($availableQtyToBack, $qtyBackToSource);
                $originalProcessedQty += $data[$sku];

                // check if source has some qty of SKU, so it's possible to take them into account
                if ($this->isZero((float)$availableQtyToBack)) {
                    continue;
                }

                if ($backQty > 0) {
                    $sourceItem = $this->getSourceItemBySourceCodeAndSku->execute($sourceCode, $sku);
                    $sourceItem->setQuantity($sourceItem->getQuantity() + $backQty);
                    $sourceItemToSave[] = $sourceItem;
                    $this->sourceItemsSave->execute($sourceItemToSave);
                }

                $qtyBackToSource -= $backQty;
            }

            if ($qtyBackToSource > 0) {
                $itemToSell = $this->itemsToSellFactory->create([
                    'sku' => $sku,
                    'qty' => (float)$qtyBackToSource
                ]);

                $websiteId = (int)$order->getStore()->getWebsiteId();
                $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();

                /** @var SalesEventInterface $salesEvent */
                $salesEvent = $this->salesEventFactory->create([
                    'type' => SalesEventInterface::EVENT_CREDITMEMO_CREATED,
                    'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
                    'objectId' => (string)$order->getEntityId()
                ]);

                $salesChannel = $this->salesChannelFactory->create([
                    'data' => [
                        'type' => SalesChannelInterface::TYPE_WEBSITE,
                        'code' => $websiteCode
                    ]
                ]);

                $this->placeReservationsForSalesEvent->execute([$itemToSell], $salesChannel, $salesEvent);
            }
        }
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
