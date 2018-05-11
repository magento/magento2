<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventoryShipping\Model\SourceDeduction\Request\ItemToDeductInterface;
use Magento\InventoryShipping\Model\SourceDeduction\Request\ItemToDeductInterfaceFactory;
use Magento\InventoryShipping\Model\SourceDeduction\Request\SourceDeductionRequestInterface;
use Magento\InventoryShipping\Model\SourceDeduction\Request\SourceDeductionRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;

class SourceDeductionRequestsFromSourceSelectionFactory
{
    /**
     * @var SourceDeductionRequestInterfaceFactory
     */
    private $sourceDeductionRequestFactory;

    /**
     * @var ItemToDeductInterfaceFactory
     */
    private $itemToDeductFactory;

    /**
     * @param SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory
     * @param ItemToDeductInterfaceFactory $itemToDeductFactory
     */
    public function __construct(
        SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory,
        ItemToDeductInterfaceFactory $itemToDeductFactory
    ) {
        $this->sourceDeductionRequestFactory = $sourceDeductionRequestFactory;
        $this->itemToDeductFactory = $itemToDeductFactory;
    }

    /**
     * @param SourceSelectionResultInterface $sourceSelectionResult
     * @param SalesEventInterface $salesEvent
     * @param int $websiteId
     * @return SourceDeductionRequestInterface[]
     */
    public function create(
        SourceSelectionResultInterface $sourceSelectionResult,
        SalesEventInterface $salesEvent,
        int $websiteId
    ): array {
        $sourceDeductionRequests = [];
        foreach ($this->getItemsPerSource($sourceSelectionResult->getSourceSelectionItems()) as $sourceCode => $items) {
            /** @var SourceDeductionRequestInterface[] $sourceDeductionRequests */
            $sourceDeductionRequests[] = $this->sourceDeductionRequestFactory->create([
                'websiteId' => $websiteId,
                'sourceCode' => $sourceCode,
                'items' => $items,
                'salesEvent' => $salesEvent
            ]);
        }
        return $sourceDeductionRequests;
    }

    /**
     * @param SourceSelectionItemInterface[] $sourceSelectionItems
     * @return ItemToDeductInterface[]
     */
    private function getItemsPerSource(array $sourceSelectionItems)
    {
        $itemsPerSource = [];
        foreach ($sourceSelectionItems as $sourceSelectionItem) {
            if ($sourceSelectionItem->getQtyToDeduct() === 0) {
                continue;
            }
            if (!isset($itemsPerSource[$sourceSelectionItem->getSourceCode()])) {
                $itemsPerSource[$sourceSelectionItem->getSourceCode()] = [];
            }
            $itemsPerSource[$sourceSelectionItem->getSourceCode()][] = $this->itemToDeductFactory->create([
                'sku' => $sourceSelectionItem->getSku(),
                'qty' => $sourceSelectionItem->getQtyToDeduct(),
            ]);
        }
        return $itemsPerSource;
    }
}
