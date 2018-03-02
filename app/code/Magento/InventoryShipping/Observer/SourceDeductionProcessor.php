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
     * SourceDeductionProcessor constructor.
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku
     */
    public function __construct(
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        SourceItemsSaveInterface $sourceItemsSave,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku
    ) {
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourceItemBySourceCodeAndSku = $getSourceItemBySourceCodeAndSku;
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
            $reservationToSave = [];
            foreach ($sources as $source) {
                $source_code = $source['sourceCode'];
                $qty = $source['qtyToDeduct'];
                $sourceItem = $this->getSourceItemBySourceCodeAndSku->execute($source_code, $item->getSku());
                //TODO: need to implement additional checks
                // with backorder+when source disabled or product OutOfStock
                if (($sourceItem->getQuantity() - $qty) >= 0) {
                    $sourceItem->setQuantity($sourceItem->getQuantity() - $qty);
                    $sourceItemToSave[] = $sourceItem;
                    $reservationToSave[] = $reservation = $this->reservationBuilder
                        ->setSku($item->getSku())
                        ->setQuantity($qty)
                        ->setStockId($stockId)
                        ->build();

                    //TODO: add data to history order_item_id|source_code|qty
                } else {
                    throw new LocalizedException(__('Negative quantity is not allowed.'));
                }
            }
            $this->sourceItemsSave->execute($sourceItemToSave);
            $this->appendReservations->execute($reservationToSave);
        }
    }
}
