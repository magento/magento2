<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySourceDeductionApi\Model\ItemToDeductInterface;
use Magento\InventorySourceDeductionApi\Model\ItemToDeductInterfaceFactory;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

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
     * @var SalesChannelInterfaceFactory
     */
    private $salesChannelFactory;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @param SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory
     * @param ItemToDeductInterfaceFactory $itemToDeductFactory
     * @param SalesChannelInterfaceFactory $salesChannelFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        SourceDeductionRequestInterfaceFactory $sourceDeductionRequestFactory,
        ItemToDeductInterfaceFactory $itemToDeductFactory,
        SalesChannelInterfaceFactory $salesChannelFactory,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->sourceDeductionRequestFactory = $sourceDeductionRequestFactory;
        $this->itemToDeductFactory = $itemToDeductFactory;
        $this->salesChannelFactory = $salesChannelFactory;
        $this->websiteRepository = $websiteRepository;
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
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();
        $salesChannel = $this->salesChannelFactory->create([
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $websiteCode
            ]
        ]);

        foreach ($this->getItemsPerSource($sourceSelectionResult->getSourceSelectionItems()) as $sourceCode => $items) {
            /** @var SourceDeductionRequestInterface[] $sourceDeductionRequests */
            $sourceDeductionRequests[] = $this->sourceDeductionRequestFactory->create([
                'sourceCode' => $sourceCode,
                'items' => $items,
                'salesChannel' => $salesChannel,
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
            if ($sourceSelectionItem->getQtyToDeduct() < 0.000001) {
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
