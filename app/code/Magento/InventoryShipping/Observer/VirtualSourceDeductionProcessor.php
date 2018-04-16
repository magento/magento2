<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryReservationsApi\Api\Data\ReservationInterface;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryShipping\Model\GetSourceItemBySourceCodeAndSku;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryShipping\Model\SourceSelection\GetDefaultSourceSelectionAlgorithmCodeInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
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
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $getDefaultSourceSelectionAlgorithmCode;

    /**
     * @var ReservationInterface[]
     */
    private $reservations = [];

    /**
     * VirtualSourceDeductionProcessor constructor.
     * @param ReservationBuilderInterface $reservationBuilder
     * @param AppendReservationsInterface $appendReservations
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param GetSourceItemBySourceCodeAndSku $getSourceItemBySourceCodeAndSku
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param Json $jsonSerializer
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
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
        Json $jsonSerializer,
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        ItemRequestInterfaceFactory $itemRequestFactory,
        SourceSelectionServiceInterface $sourceSelectionService,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
    )
    {
        $this->reservationBuilder = $reservationBuilder;
        $this->appendReservations = $appendReservations;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourceItemBySourceCodeAndSku = $getSourceItemBySourceCodeAndSku;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->jsonSerializer = $jsonSerializer;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->itemRequestFactory = $itemRequestFactory;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getEvent()->getInvoice();

        if ($invoice->getOrigData('entity_id')) {
            return;
        }

        $selectionRequestItems = [];
        foreach ($invoice->getItems() as $invoiceItem) {
            if (!$this->isValidItem($invoiceItem)) {
                continue;
            }

            $itemSku = $invoiceItem->getSku() ?: $this->getSkusByProductIds->execute(
                [$invoiceItem->getProductId()]
            )[$invoiceItem->getProductId()];
            $qty = $this->castQty($invoiceItem->getOrderItem(), $invoiceItem->getQty());

            $selectionRequestItems[] = $this->itemRequestFactory->create([
                'sku' => $itemSku,
                'qty' => $qty,
            ]);
        }

        if (empty($selectionRequestItems)) {
            return;
        }

        $order = $invoice->getOrder();
        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();

        /** @var InventoryRequestInterface $inventoryRequest */
        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items' => $selectionRequestItems
        ]);
        $sourceSelectionResult = $this->sourceSelectionService->execute(
            $inventoryRequest,
            $this->getDefaultSourceSelectionAlgorithmCode->execute()
        );

        $this->deductSources($sourceSelectionResult, $stockId);
    }

    /**
     * @param InvoiceItemInterface $invoiceItem
     * @return bool
     */
    private function isValidItem(InvoiceItemInterface $invoiceItem): bool
    {
        $orderItem = $invoiceItem->getOrderItem();
        return in_array(
            $orderItem->getProductType(), [
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
            ]
        );
    }

    /**
     * @param SourceSelectionResultInterface $sourceSelectionResult
     * @param int $stockId
     *
     * @throws ValidationException
     * @throws CouldNotSaveException
     * @throws InputException
     */
    private function deductSources(SourceSelectionResultInterface $sourceSelectionResult, int $stockId)
    {
        $sourceItemsToSave = [];
        foreach ($sourceSelectionResult->getSourceSelectionItems() as $sourceSelectionItem) {
            $deductQty = $sourceSelectionItem->getQtyToDeduct();
            if ($deductQty <= 0) {
                 continue;
            }

            $sourceItem = $this->getSourceItemBySourceCodeAndSku->execute(
                $sourceSelectionItem->getSourceCode(),
                $sourceSelectionItem->getSku()
            );
            $sourceItem->setQuantity($sourceItem->getQuantity() - $deductQty);
            $sourceItemsToSave[] = $sourceItem;

            $this->buildReservation($stockId, $sourceSelectionItem);
        }

        $this->sourceItemsSave->execute($sourceItemsToSave);
        $this->appendReservations->execute($this->reservations);
    }

    /**
     * @param int $stockId
     * @param SourceSelectionItemInterface $sourceSelectionItem
     * @throws ValidationException
     */
    private function buildReservation(int $stockId, SourceSelectionItemInterface $sourceSelectionItem)
    {
        $sku = $sourceSelectionItem->getSku();
        $key = $sku . '_' . $stockId;
        $qtyToDeduct = $sourceSelectionItem->getQtyToDeduct();

        if (isset($this->reservations[$key])) {
            $qtyToDeduct += $this->reservations[$key]->getQuantity();
        }

        $this->reservations[$key] = $this->reservationBuilder
            ->setSku($sourceSelectionItem->getSku())
            ->setStockId($stockId)
            ->setQuantity($qtyToDeduct)
            ->build();
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
