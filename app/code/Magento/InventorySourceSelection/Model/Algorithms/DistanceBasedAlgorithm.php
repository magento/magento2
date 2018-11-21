<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\Algorithms;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterfaceFactory;
use Magento\InventorySourceSelectionApi\Model\GetGeoReferenceProvider;
use Magento\InventorySourceSelectionApi\Model\SourceSelectionInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * {@inheritdoc}
 * This shipping algorithm just iterates over all the sources one by one in distance order
 */
class DistanceBasedAlgorithm implements SourceSelectionInterface
{
    /**
     * @var GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    private $getSourcesAssignedToStockOrderedByPriority;

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
     * @var GetGeoReferenceProvider
     */
    private $getGeoReferenceProvider;

    /**
     * DistanceBasedAlgorithm constructor.
     *
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     * @param SourceSelectionItemInterfaceFactory $sourceSelectionItemFactory
     * @param SourceSelectionResultInterfaceFactory $sourceSelectionResultFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param GetGeoReferenceProvider $getGeoReferenceProvider
     */
    public function __construct(
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        SourceSelectionItemInterfaceFactory $sourceSelectionItemFactory,
        SourceSelectionResultInterfaceFactory $sourceSelectionResultFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemRepositoryInterface $sourceItemRepository,
        GetGeoReferenceProvider $getGeoReferenceProvider
    ) {
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
        $this->sourceSelectionItemFactory = $sourceSelectionItemFactory;
        $this->sourceSelectionResultFactory = $sourceSelectionResultFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->getGeoReferenceProvider = $getGeoReferenceProvider;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute(InventoryRequestInterface $inventoryRequest): SourceSelectionResultInterface
    {
        $isShippable = true;
        $destinationAddress = $inventoryRequest->getExtensionAttributes()->getDestinationAddress();
        if ($destinationAddress === null) {
            throw new LocalizedException(__('No destination address was provided in the request'));
        }

        $stockId = $inventoryRequest->getStockId();
        $sources = $this->getEnabledSourcesOrderedByDistanceByStockId(
            $stockId,
            $destinationAddress
        );
        $sourceItemSelections = [];

        foreach ($inventoryRequest->getItems() as $item) {
            $itemSku = $item->getSku();
            $qtyToDeliver = $item->getQty();
            foreach ($sources as $source) {
                $sourceItem = $this->getSourceItemBySourceCodeAndSku($source->getSourceCode(), $itemSku);
                if (null === $sourceItem) {
                    continue;
                }

                if ($sourceItem->getStatus() !== SourceItemInterface::STATUS_IN_STOCK) {
                    continue;
                }

                $sourceItemQty = $sourceItem->getQuantity();
                $qtyToDeduct = min($sourceItemQty, $qtyToDeliver);

                // check if source has some qty of SKU, so it's possible to take them into account
                if ($this->isZero((float)$sourceItemQty)) {
                    continue;
                }

                $sourceItemSelections[] = $this->sourceSelectionItemFactory->create([
                    'sourceCode' => $sourceItem->getSourceCode(),
                    'sku' => $itemSku,
                    'qtyToDeduct' => $qtyToDeduct,
                    'qtyAvailable' => $sourceItemQty
                ]);

                $qtyToDeliver -= $qtyToDeduct;
            }

            // if we go through all sources from the stock and there is still some qty to delivery,
            // then it doesn't have enough items to delivery
            if (!$this->isZero($qtyToDeliver)) {
                $isShippable = false;
            }
        }

        return $this->sourceSelectionResultFactory->create(
            [
                'sourceItemSelections' => $sourceItemSelections,
                'isShippable' => $isShippable
            ]
        );
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
     * Get enabled sources ordered by priority by $stockId
     *
     * @param int $stockId
     * @param AddressRequestInterface $addressRequest
     * @return array
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getEnabledSourcesOrderedByDistanceByStockId(
        int $stockId,
        AddressRequestInterface $addressRequest
    ): array {
        // We keep priority order as computational base
        $sources = $this->getSourcesAssignedToStockOrderedByPriority->execute($stockId);
        $sources = array_filter($sources, function (SourceInterface $source) {
            return $source->isEnabled();
        });

        $geoReferenceProvider = $this->getGeoReferenceProvider->execute();

        // Sort sources by distance
        uasort(
            $sources,
            function (SourceInterface $a, SourceInterface $b) use ($geoReferenceProvider, $addressRequest) {
                $distanceFromA = $geoReferenceProvider->getDistance($a, $addressRequest);
                $distanceFromB = $geoReferenceProvider->getDistance($b, $addressRequest);

                return ($distanceFromA < $distanceFromB) ? -1 : 1;
            }
        );

        return $sources;
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
}
