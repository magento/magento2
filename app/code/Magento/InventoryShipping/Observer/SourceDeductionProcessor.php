<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryShipping\Model\GetSourceItemBySourceCodeAndSku;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;

class SourceDeductionProcessor implements ObserverInterface
{
    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var GetSourceItemBySourceCodeAndSku
     */
    private $getSourceItemBySourceCodeAndSku;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * SourceDeductionProcessor constructor.
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     */
    public function __construct(
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        SourceItemsSaveInterface $sourceItemsSave,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku,
        GetSkusByProductIdsInterface $getSkusByProductIds
    ) {
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourceItemBySourceCodeAndSku = $getSourceItemBySourceCodeAndSku;
        $this->getSkusByProductIds = $getSkusByProductIds;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $observer->getEvent()->getShipment();
        if ($shipment->getOrigData('entity_id')) {
            return $this;
        }

        $order = $shipment->getOrder();

        // I'm not sure about websiteId
        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();

        foreach ($shipment->getItems() as $item) {
            $sources = $item->getSources();
            if (!$sources) {
                continue;
            }
            $sourceItemToSave = [];
            $reservationsToBuild = [];
            foreach ($sources as $source) {
                $sourceCode = $source['sourceCode'];
                $qty = $source['qtyToDeduct'];
                $itemSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                    [$item->getProductId()]
                )[$item->getProductId()];
                $sourceItem = $this->getSourceItemBySourceCodeAndSku->execute($sourceCode, $itemSku);
                //TODO: need to implement additional checks
                // with backorder+when source disabled or product OutOfStock
                if (($sourceItem->getQuantity() - $qty) >= 0) {
                    $sourceItem->setQuantity($sourceItem->getQuantity() - $qty);
                    $sourceItemToSave[] = $sourceItem;
                    $reservationsToBuild[$itemSku] = ($reservationsToBuild[$itemSku] ?? 0) + $qty;
                    //TODO: add data to history order_item_id|source_code|qty
                } else {
                    throw new LocalizedException(__('Negative quantity is not allowed.'));
                }
            }

            $reservationToSave = [];
            foreach ($reservationsToBuild as $sku => $reservationQty) {
                $reservationToSave[] = $this->reservationBuilder
                    ->setSku($sku)
                    ->setQuantity($reservationQty)
                    ->setStockId($stockId)
                    ->build();
            }
            $this->sourceItemsSave->execute($sourceItemToSave);
            $this->appendReservations->execute($reservationToSave);
        }
    }
}
