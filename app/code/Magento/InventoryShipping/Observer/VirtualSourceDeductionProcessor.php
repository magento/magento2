<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventoryShipping\Model\GetSourceSelectionResultFromInvoice;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionServiceInterface;
use Magento\InventoryShipping\Model\SourceDeductionRequestsFromSourceSelectionFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;

/**
 * Class VirtualSourceDeductionProcessor
 */
class VirtualSourceDeductionProcessor implements ObserverInterface
{
    /**
     * @var GetSourceSelectionResultFromInvoice
     */
    private $getSourceSelectionResultFromInvoice;

    /**
     * @var SourceDeductionServiceInterface
     */
    private $sourceDeductionService;

    /**
     * @var SourceDeductionRequestsFromSourceSelectionFactory
     */
    private $sourceDeductionRequestsFromSourceSelectionFactory;

    /**
     * @var SalesEventInterfaceFactory
     */
    private $salesEventFactory;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemToSellFactory;

    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

    /**
     * @param GetSourceSelectionResultFromInvoice $getSourceSelectionResultFromInvoice
     * @param SourceDeductionServiceInterface $sourceDeductionService
     * @param SourceDeductionRequestsFromSourceSelectionFactory $sourceDeductionRequestsFromSourceSelectionFactory
     * @param SalesEventInterfaceFactory $salesEventFactory
     * @param ItemToSellInterfaceFactory $itemToSellFactory
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     */
    public function __construct(
        GetSourceSelectionResultFromInvoice $getSourceSelectionResultFromInvoice,
        SourceDeductionServiceInterface $sourceDeductionService,
        SourceDeductionRequestsFromSourceSelectionFactory $sourceDeductionRequestsFromSourceSelectionFactory,
        SalesEventInterfaceFactory $salesEventFactory,
        ItemToSellInterfaceFactory $itemToSellFactory,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
    ) {
        $this->getSourceSelectionResultFromInvoice = $getSourceSelectionResultFromInvoice;
        $this->sourceDeductionService = $sourceDeductionService;
        $this->sourceDeductionRequestsFromSourceSelectionFactory = $sourceDeductionRequestsFromSourceSelectionFactory;
        $this->salesEventFactory = $salesEventFactory;
        $this->itemToSellFactory = $itemToSellFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
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
        if (!$this->isValid($invoice)) {
            return;
        }

        $sourceSelectionResult = $this->getSourceSelectionResultFromInvoice->execute($invoice);

        /** @var SalesEventInterface $salesEvent */
        $salesEvent = $this->salesEventFactory->create([
            'type' => SalesEventInterface::EVENT_INVOICE_CREATED,
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => $invoice->getOrderId(),
        ]);

        $sourceDeductionRequests = $this->sourceDeductionRequestsFromSourceSelectionFactory->create(
            $sourceSelectionResult,
            $salesEvent,
            (int)$invoice->getOrder()->getStore()->getWebsiteId()
        );

        foreach ($sourceDeductionRequests as $sourceDeductionRequest) {
            $this->sourceDeductionService->execute($sourceDeductionRequest);
            $this->placeCompensatingReservation($sourceDeductionRequest);
        }
    }

    /**
     * Place compensating reservation after source deduction
     *
     * @param SourceDeductionRequestInterface $sourceDeductionRequest
     */
    private function placeCompensatingReservation(SourceDeductionRequestInterface $sourceDeductionRequest): void
    {
        $items = [];
        foreach ($sourceDeductionRequest->getItems() as $item) {
            $items[] = $this->itemToSellFactory->create([
                'sku' => $item->getSku(),
                'qty' => $item->getQty()
            ]);
        }
        $this->placeReservationsForSalesEvent->execute(
            $items,
            $sourceDeductionRequest->getSalesChannel(),
            $sourceDeductionRequest->getSalesEvent()
        );
    }

    /**
     * @param InvoiceInterface $invoice
     * @return bool
     */
    private function isValid(InvoiceInterface $invoice): bool
    {
        if ($invoice->getOrigData('entity_id')) {
            return false;
        }

        return $this->hasValidItems($invoice);
    }

    /**
     * @param InvoiceInterface $invoice
     * @return bool
     */
    private function hasValidItems(InvoiceInterface $invoice): bool
    {
        foreach ($invoice->getItems() as $invoiceItem) {
            /** @var OrderItemInterface $orderItem */
            $orderItem = $invoiceItem->getOrderItem();
            if ($orderItem->getIsVirtual()) {
                return true;
            }
        }

        return false;
    }
}
