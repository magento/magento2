<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model\Algorithms\Result;

use Magento\Framework\App\ObjectManager;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\GetAvailableSourceItemsBySkusAndSortedSourceInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;

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
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var GetAvailableSourceItemsBySkusAndSortedSourceInterface
     */
    private $getAvailableSourceItemsBySkusAndSortedSource;

    /**
     * GetDefaultSortedSourcesResult constructor.
     *
     * @param SourceSelectionItemInterfaceFactory $sourceSelectionItemFactory
     * @param SourceSelectionResultInterfaceFactory $sourceSelectionResultFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SourceSelectionItemInterfaceFactory $sourceSelectionItemFactory,
        SourceSelectionResultInterfaceFactory $sourceSelectionResultFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemRepositoryInterface $sourceItemRepository,
        GetAvailableSourceItemsBySkusAndSortedSourceInterface $getAvailableSourceItemsBySkusAndSortedSource = null
    ) {
        $this->sourceSelectionItemFactory = $sourceSelectionItemFactory;
        $this->sourceSelectionResultFactory = $sourceSelectionResultFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->getAvailableSourceItemsBySkusAndSortedSource = $getAvailableSourceItemsBySkusAndSortedSource ?:
            ObjectManager::getInstance()->get(GetAvailableSourceItemsBySkusAndSortedSourceInterface::class);
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
     * Returns source item from specific source by given SKU. Return null if source item is not found
     *
     * @param string $sourceCode
     * @param string $sku
     * @return SourceItemInterface|null
     */
    private function getSourceItemBySourceCodeAndSku(string $sourceCode, string $sku): ?SourceItemInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();
        $sourceItemsResult = $this->sourceItemRepository->getList($searchCriteria);

        return $sourceItemsResult->getTotalCount() > 0 ? current($sourceItemsResult->getItems()) : null;
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
        $isShippable = true;
        $sourceItemSelections = [];

        $itemsTdDeliver = [];
        foreach ($inventoryRequest->getItems() as $item) {
            $itemsTdDeliver[$item->getSku()] = (float) $item->getQty();
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
