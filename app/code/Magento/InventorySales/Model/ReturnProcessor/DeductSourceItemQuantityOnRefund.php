<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventorySalesApi\Model\ReturnProcessor\Request\ItemsToRefundInterface;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionServiceInterface;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\Sales\Api\Data\OrderInterface;

class DeductSourceItemQuantityOnRefund
{
    /**
     * @var GetSourceSelectionResultFromCreditMemoItems
     */
    private $getSourceSelectionResultFromCreditMemoItems;

    /**
     * @var GetSourceDeductionRequestFromSourceSelection
     */
    private $getSourceDeductionRequestFromSourceSelection;

    /**
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * @var SourceDeductionServiceInterface
     */
    private $sourceDeductionService;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemsToSellFactory;

    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

    /**
     * @param GetSourceSelectionResultFromCreditMemoItems $getSourceSelectionResultFromCreditMemoItems
     * @param GetSourceDeductionRequestFromSourceSelection $getSourceDeductionRequestFromSourceSelection
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param SourceDeductionServiceInterface $sourceDeductionService
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     */
    public function __construct(
        GetSourceSelectionResultFromCreditMemoItems $getSourceSelectionResultFromCreditMemoItems,
        GetSourceDeductionRequestFromSourceSelection $getSourceDeductionRequestFromSourceSelection,
        SourceSelectionServiceInterface $sourceSelectionService,
        SourceDeductionServiceInterface $sourceDeductionService,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
    ) {
        $this->getSourceSelectionResultFromCreditMemoItems = $getSourceSelectionResultFromCreditMemoItems;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->sourceDeductionService = $sourceDeductionService;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->getSourceDeductionRequestFromSourceSelection = $getSourceDeductionRequestFromSourceSelection;
    }

    /**
     * @param OrderInterface $order
     * @param ItemsToRefundInterface[] $itemsToRefund
     * @param array $itemsToDeductFromSource
     */
    public function execute(
        OrderInterface $order,
        array $itemsToRefund,
        array $itemsToDeductFromSource
    ): void {
        $sourceSelectionResult = $this->getSourceSelectionResultFromCreditMemoItems->execute(
            $order,
            $itemsToRefund,
            $itemsToDeductFromSource
        );

        $sourceDeductionRequests = $this->getSourceDeductionRequestFromSourceSelection->execute(
            $order,
            $sourceSelectionResult
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
            $items[] = $this->itemsToSellFactory->create([
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
}
