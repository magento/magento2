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
     * @return SourceDeductionProcessor
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $observer->getEvent()->getShipment();
        if ($shipment->getOrigData('entity_id')) {
            return $this;
        }

        $order = $shipment->getOrder();

        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();
        $sourceCode = (string)$shipment->getExtensionAttributes()->getSourceCode();

        $sourceItemToSave = [];
        $reservationsToBuild = [];

        foreach ($shipment->getItems() as $item) {
            $qty = $item->getQty();

            // TODO: Need to add logic for configurable/bundle/grouped product
            // This functionality should work only with simple products
            if (!$qty) {
                continue;
            }
            $itemSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                [$item->getProductId()]
            )[$item->getProductId()];
            $sourceItem = $this->getSourceItemBySourceCodeAndSku->execute($sourceCode, $itemSku);

            if (empty($sourceItem)) {
                continue;
            }
            //TODO: need to implement additional checks
            // when source disabled or product OutOfStock etc + manage stock
            if (($sourceItem->getQuantity() - $qty) >= 0) {
                $sourceItem->setQuantity($sourceItem->getQuantity() - $qty);
                $sourceItemToSave[] = $sourceItem;
                $reservationsToBuild[$itemSku] = ($reservationsToBuild[$itemSku] ?? 0) + $qty;
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
