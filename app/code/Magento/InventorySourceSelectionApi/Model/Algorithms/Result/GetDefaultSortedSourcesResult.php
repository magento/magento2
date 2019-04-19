<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model\Algorithms\Result;

use Magento\Framework\App\ObjectManager;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventorySourceSelectionApi\Model\GetAvailableSourceItemsBySkusAndSortedSource;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterfaceFactory;

/**
 * Return a default response for sorted source algorithms
 */
class GetDefaultSortedSourcesResult
{
    /**
     * @var SourceSelectionItemInterfaceFactory
     */
    private $sourceSelectionItemFactory;

    /**
     * @var SourceSelectionResultInterfaceFactory
     */
    private $sourceSelectionResultFactory;

    /**
     * @var GetAvailableSourceItemsBySkusAndSortedSource
     */
    private $getAvailableSourceItemsBySkusAndSortedSource;

    /**
     * @param SourceSelectionItemInterfaceFactory $sourceSelectionItemFactory
     * @param SourceSelectionResultInterfaceFactory $sourceSelectionResultFactory
     * @param null $searchCriteriaBuilder @deprecated
     * @param null $sourceItemRepository @deprecated
     * @param GetAvailableSourceItemsBySkusAndSortedSource $getAvailableSourceItemsBySkusAndSortedSource = null
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SourceSelectionItemInterfaceFactory $sourceSelectionItemFactory,
        SourceSelectionResultInterfaceFactory $sourceSelectionResultFactory,
        $searchCriteriaBuilder,
        $sourceItemRepository,
        GetAvailableSourceItemsBySkusAndSortedSource $getAvailableSourceItemsBySkusAndSortedSource = null
    ) {
        $this->sourceSelectionItemFactory = $sourceSelectionItemFactory;
        $this->sourceSelectionResultFactory = $sourceSelectionResultFactory;
        $this->getAvailableSourceItemsBySkusAndSortedSource = $getAvailableSourceItemsBySkusAndSortedSource ?:
            ObjectManager::getInstance()->get(GetAvailableSourceItemsBySkusAndSortedSource::class);
    }

    /**
     * Compare float number with some epsilon
     *
     * @param float $floatNumber
     *
     * @return bool
     */
    private function isZero(float $floatNumber): bool
    {
        return $floatNumber < 0.0000001;
    }

    /**
     * Generate default result for priority based algorithms
     *
     * @param InventoryRequestInterface $inventoryRequest
     * @param SourceInterface[] $sortedSources
     * @return SourceSelectionResultInterface
     */
    public function execute(
        InventoryRequestInterface $inventoryRequest,
        array $sortedSources
    ): SourceSelectionResultInterface {
        $sourceItemSelections = [];

        $itemsTdDeliver = [];
        foreach ($inventoryRequest->getItems() as $item) {
            $itemsTdDeliver[$item->getSku()] = $item->getQty();
        }

        $sortedSourceCodes = [];
        foreach ($sortedSources as $sortedSource) {
            $sortedSourceCodes[] = $sortedSource->getSourceCode();
        }

        $sourceItems =
            $this->getAvailableSourceItemsBySkusAndSortedSource->execute(
                array_keys($itemsTdDeliver),
                $sortedSourceCodes
            );

        foreach ($sourceItems as $sourceItem) {
            $qtyToDeduct = min($sourceItem->getQuantity(), $itemsTdDeliver[$sourceItem->getSku()] ?? 0.0);

            $sourceItemSelections[] = $this->sourceSelectionItemFactory->create([
                'sourceCode' => $sourceItem->getSourceCode(),
                'sku' => $sourceItem->getSku(),
                'qtyToDeduct' => $qtyToDeduct,
                'qtyAvailable' => $sourceItem->getQuantity()
            ]);

            $itemsTdDeliver[$sourceItem->getSku()] -= $qtyToDeduct;
        }

        $isShippable = true;
        foreach ($itemsTdDeliver as $itemToDeliver) {
            if (!$this->isZero($itemToDeliver)) {
                $isShippable = false;
                break;
            }
        }

        return $this->sourceSelectionResultFactory->create(
            [
                'sourceItemSelections' => $sourceItemSelections,
                'isShippable' => $isShippable
            ]
        );
    }
}
