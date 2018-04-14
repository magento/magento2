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
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;

/**
 * Class VirtualSourceDeductionProcessor | Probably need to divide on services
 */
class VirtualSourceDeductionProcessor implements ObserverInterface
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
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param DefaultSourceProviderInterface $defaultSourceProvider
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
        DefaultSourceProviderInterface $defaultSourceProvider,
        Json $jsonSerializer
    ) {
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourceItemBySourceCodeAndSku = $getSourceItemBySourceCodeAndSku;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $shipment */
        $invoice = $observer->getEvent()->getInvoice();

        if ($invoice->getOrigData('entity_id')) {
            return;
        }

        // For virtual we assume that only the default source is used
        $sourceCode = $this->defaultSourceProvider->getCode();
        $order      = $invoice->getOrder();

        $websiteId = $order->getStore()->getWebsiteId();
        $stockId   = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();

        foreach ($invoice->getItems() as $invoiceItem) {
            $orderItem       = $invoiceItem->getOrderItem();
            $itemsToDecrease = [];

            if (in_array(
                $orderItem->getProductType(), [
                    \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                    \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ]
            )) {
                $itemSku = $invoiceItem->getSku() ? : $this->getSkusByProductIds->execute(
                    [$invoiceItem->getProductId()]
                )[$invoiceItem->getProductId()];

                $qty = $this->castQty($orderItem, $invoiceItem->getQty());

                $itemsToDecrease[] = [
                    'sku' => $itemSku,
                    'qty' => $qty
                ];
            }

            if (!empty($itemsToDecrease)) {
                $this->processItems($stockId, $sourceCode, $itemsToDecrease);
            }
        }

        return;
    }

    /**
     * @param $stockId
     * @param $sourceCode
     * @param $itemsToDecrease
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    private function processItems($stockId, $sourceCode, $itemsToDecrease)
    {
        $sourceItemToSave = [];
        $reservationsToBuild = [];

        foreach ($itemsToDecrease as $item) {
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
