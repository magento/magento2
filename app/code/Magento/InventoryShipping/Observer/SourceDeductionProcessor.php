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
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class SourceDeductionProcessor | Probably need to divide on services
 */
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
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param Json $jsonSerializer
     */
    public function __construct(
        ReservationBuilderInterface $reservationBuilder,
        AppendReservationsInterface $appendReservations,
        SourceItemsSaveInterface $sourceItemsSave,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        Json $jsonSerializer
    ) {
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourceItemBySourceCodeAndSku = $getSourceItemBySourceCodeAndSku;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $shipmentItem = $observer->getEvent()->getShipmentItem();

        if ($shipmentItem->getOrigData('entity_id')) {
            return;
        }

        $shipment = $shipmentItem->getShipment();
        if (empty($shipment->getExtensionAttributes())
            || !$shipment->getExtensionAttributes()->getSourceCode()) {
            throw new LocalizedException(__('Source not specified.'));
        }
        $sourceCode = $shipment->getExtensionAttributes()->getSourceCode();
        $orderItem = $shipmentItem->getOrderItem();
        $itemSku = $shipmentItem->getSku() ?: $this->getSkusByProductIds->execute(
            [$shipmentItem->getProductId()]
        )[$shipmentItem->getProductId()];
        $qty = $this->castQty($orderItem, $shipmentItem->getQty());
        $itemsToShip = [];
        if ($orderItem->getHasChildren()) {
            if (!$orderItem->isDummy(true)) {
                foreach ($orderItem->getChildrenItems() as $item) {
                    if ($item->getIsVirtual() || $item->getLockedDoShip()) {
                        continue;
                    }
                    $productOptions = $item->getProductOptions();
                    if (isset($productOptions['bundle_selection_attributes'])) {
                        $bundleSelectionAttributes = $this->jsonSerializer->unserialize(
                            $productOptions['bundle_selection_attributes']
                        );
                        if ($bundleSelectionAttributes) {
                            $qty = $bundleSelectionAttributes['qty'] * $shipmentItem->getQty();
                            $qty = $this->castQty($item, $qty);
                            $itemSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                                [$item->getProductId()]
                            )[$item->getProductId()];
                            $itemsToShip[] = [
                                'sku' => $itemSku,
                                'qty' => $qty
                            ];
                            continue;
                        }
                    } else {
                        // configurable product
                        $itemsToShip[] = [
                            'sku' => $itemSku,
                            'qty' => $qty
                        ];
                    }
                }
            }
        } else {
            $itemsToShip[] = [
                'sku' => $itemSku,
                'qty' => $qty
            ];
        }

        $websiteId = $shipmentItem->getShipment()->getOrder()->getStore()->getWebsiteId();
        $this->processItems($websiteId, $sourceCode, $itemsToShip);

        return;
    }

    /**
     * @param $websiteId
     * @param $sourceCode
     * @param $itemsToShip
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    private function processItems($websiteId, $sourceCode, $itemsToShip)
    {
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();
        $sourceItemToSave = [];
        $reservationsToBuild = [];

        foreach ($itemsToShip as $item) {
            $itemSku = $item['sku'];
            $qty = $item['qty'];
            $stockItemConfiguration = $this->getStockItemConfiguration->execute(
                $itemSku,
                $stockId
            );
            if (!$stockItemConfiguration->isManageStock()) {
                continue;
            }
            $sourceItem = $this->getSourceItemBySourceCodeAndSku->execute($sourceCode, $itemSku);
            if (($sourceItem->getQuantity() - $qty) >= 0) {
                $sourceItem->setQuantity($sourceItem->getQuantity() - $qty);
                $sourceItemToSave[] = $sourceItem;
                $reservationsToBuild[$itemSku] = ($reservationsToBuild[$itemSku] ?? 0) + $qty;
            } else {
                throw new LocalizedException(
                    __('Not all of your products are available in the requested quantity.')
                );
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

    /**
     * @param Item $item
     * @param string|int|float $qty
     * @return float|int
     */
    private function castQty(Item $item, $qty)
    {
        if ($item->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }

        return $qty > 0 ? $qty : 0;
    }
}
