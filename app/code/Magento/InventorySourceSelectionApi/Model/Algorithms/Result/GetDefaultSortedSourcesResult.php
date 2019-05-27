<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @noinspection PhpUnusedParameterInspection */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model\Algorithms\Result;

use Magento\Framework\App\ObjectManager;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterfaceFactory;
use Magento\InventorySourceSelectionApi\Model\GetInStockSourceItemsBySkusAndSortedSource;
use Magento\InventorySourceSelectionApi\Model\GetSourceItemQtyAvailableInterface;

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
     * @var GetInStockSourceItemsBySkusAndSortedSource
     */
    private $getInStockSourceItemsBySkusAndSortedSource;

    /**
     * @var GetSourceItemQtyAvailableInterface
     */
    private $getSourceItemQtyAvailable;

    /**
     * @param SourceSelectionItemInterfaceFactory $sourceSelectionItemFactory
     * @param SourceSelectionResultInterfaceFactory $sourceSelectionResultFactory
     * @param null $searchCriteriaBuilder @deprecated
     * @param null $sourceItemRepository @deprecated
     * @param GetInStockSourceItemsBySkusAndSortedSource $getInStockSourceItemsBySkusAndSortedSource = null
     * @param GetSourceItemQtyAvailableInterface|null $getSourceItemQtyAvailable
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        SourceSelectionItemInterfaceFactory $sourceSelectionItemFactory,
        SourceSelectionResultInterfaceFactory $sourceSelectionResultFactory,
        $searchCriteriaBuilder,
        $sourceItemRepository,
        GetInStockSourceItemsBySkusAndSortedSource $getInStockSourceItemsBySkusAndSortedSource = null,
        GetSourceItemQtyAvailableInterface $getSourceItemQtyAvailable = null
    ) {
        $this->sourceSelectionItemFactory = $sourceSelectionItemFactory;
        $this->sourceSelectionResultFactory = $sourceSelectionResultFactory;
        $this->getInStockSourceItemsBySkusAndSortedSource = $getInStockSourceItemsBySkusAndSortedSource ?:
            ObjectManager::getInstance()->get(GetInStockSourceItemsBySkusAndSortedSource::class);
        $this->getSourceItemQtyAvailable = $getSourceItemQtyAvailable ??
            ObjectManager::getInstance()->get(GetSourceItemQtyAvailableInterface::class);
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
            $this->getInStockSourceItemsBySkusAndSortedSource->execute(
                array_keys($itemsTdDeliver),
                $sortedSourceCodes
            );

        foreach ($sourceItems as $sourceItem) {
            $sourceItemQtyAvailable = $this->getSourceItemQtyAvailable->execute($sourceItem);
            $qtyToDeduct = min($sourceItemQtyAvailable, $itemsTdDeliver[$sourceItem->getSku()] ?? 0.0);

            $sourceItemSelections[] = $this->sourceSelectionItemFactory->create([
                'sourceCode' => $sourceItem->getSourceCode(),
                'sku' => $sourceItem->getSku(),
                'qtyToDeduct' => $qtyToDeduct,
                'qtyAvailable' => $sourceItemQtyAvailable
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
