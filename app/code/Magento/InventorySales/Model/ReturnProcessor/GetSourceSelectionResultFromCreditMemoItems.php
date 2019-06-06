<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\InventorySalesApi\Model\ReturnProcessor\GetSourceDeductedOrderItemsInterface;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\InventorySourceSelectionApi\Model\GetInventoryRequestFromOrder;
use Magento\Sales\Api\Data\OrderInterface;

class GetSourceSelectionResultFromCreditMemoItems
{
    /**
     * @var GetSourceDeductedOrderItemsInterface
     */
    private $getSourceDeductedOrderItems;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var GetInventoryRequestFromOrder
     */
    private $getInventoryRequestFromOrder;

    /**
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $getDefaultSourceSelectionAlgorithmCode;

    /**
     * @param GetSourceDeductedOrderItemsInterface $getSourceDeductedOrderItems
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param GetInventoryRequestFromOrder $getInventoryRequestFromOrder
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     */
    public function __construct(
        GetSourceDeductedOrderItemsInterface $getSourceDeductedOrderItems,
        ItemRequestInterfaceFactory $itemRequestFactory,
        GetInventoryRequestFromOrder $getInventoryRequestFromOrder,
        SourceSelectionServiceInterface $sourceSelectionService,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
    ) {
        $this->getSourceDeductedOrderItems = $getSourceDeductedOrderItems;
        $this->itemRequestFactory = $itemRequestFactory;
        $this->getInventoryRequestFromOrder = $getInventoryRequestFromOrder;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
    }

    /**
     * @param OrderInterface $order
     * @param array $itemsToRefund
     * @param array $itemsToDeductFromSource
     * @return SourceSelectionResultInterface
     */
    public function execute(
        OrderInterface $order,
        array $itemsToRefund,
        array $itemsToDeductFromSource
    ): SourceSelectionResultInterface {
        $deductedItems = $this->getSourceDeductedOrderItems->execute($order, $itemsToDeductFromSource);
        $requestItems = [];
        foreach ($itemsToRefund as $item) {
            $sku = $item->getSku();

            $totalDeductedQty = $this->getTotalDeductedQty($item, $deductedItems);
            $processedQty = $item->getProcessedQuantity() - $totalDeductedQty;
            $backQty = ($processedQty > 0) ? $item->getQuantity() - $processedQty : $item->getQuantity();
            $qtyBackToSource = ($backQty > 0) ? $item->getQuantity() - $backQty : $item->getQuantity();

            $requestItems[] = $this->itemRequestFactory->create([
                'sku' => $sku,
                'qty' => (float)$qtyBackToSource
            ]);
        }

        $inventoryRequest = $this->getInventoryRequestFromOrder->execute((int)$order->getEntityId(), $requestItems);
        $selectionAlgorithmCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();
        
        return $this->sourceSelectionService->execute($inventoryRequest, $selectionAlgorithmCode);
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
}
