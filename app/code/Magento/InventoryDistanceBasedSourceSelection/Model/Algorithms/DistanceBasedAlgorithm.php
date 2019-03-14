<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\Algorithms;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\GetDistanceFromSourceToAddress;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterface;
use Magento\InventorySourceSelectionApi\Model\Algorithms\Result\GetDefaultSortedSourcesResult;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Model\SourceSelectionInterface;

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
     * @var GetDefaultSortedSourcesResult
     */
    private $getDefaultSortedSourcesResult;

    /**
     * @var GetDistanceFromSourceToAddress
     */
    private $getDistanceFromSourceToAddress;

    /**
     * DistanceBasedAlgorithm constructor.
     *
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     * @param GetDefaultSortedSourcesResult $getDefaultSortedSourcesResult
     * @param GetDistanceFromSourceToAddress $getDistanceFromSourceToAddress
     */
    public function __construct(
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        GetDefaultSortedSourcesResult $getDefaultSortedSourcesResult,
        GetDistanceFromSourceToAddress $getDistanceFromSourceToAddress
    ) {
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
        $this->getDefaultSortedSourcesResult = $getDefaultSortedSourcesResult;
        $this->getDistanceFromSourceToAddress = $getDistanceFromSourceToAddress;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute(InventoryRequestInterface $inventoryRequest): SourceSelectionResultInterface
    {
        $destinationAddress = $inventoryRequest->getExtensionAttributes()->getDestinationAddress();
        if ($destinationAddress === null) {
            throw new LocalizedException(__('No destination address was provided in the request'));
        }

        $stockId = $inventoryRequest->getStockId();
        $sortedSources = $this->getEnabledSourcesOrderedByDistanceByStockId(
            $stockId,
            $destinationAddress
        );

        return $this->getDefaultSortedSourcesResult->execute($inventoryRequest, $sortedSources);
    }

    /**
     * Get enabled sources ordered by priority by $stockId
     *
     * @param int $stockId
     * @param AddressInterface $address
     * @return array
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getEnabledSourcesOrderedByDistanceByStockId(
        int $stockId,
        AddressInterface $address
    ): array {
        // We keep priority order as computational base
        $sources = $this->getSourcesAssignedToStockOrderedByPriority->execute($stockId);
        $sources = array_filter($sources, function (SourceInterface $source) {
            return $source->isEnabled();
        });

        $distanceBySourceCode = $sortSources = $sourcesWithoutDistance = [];
        foreach ($sources as $source) {
            try {
                $distanceBySourceCode[$source->getSourceCode()] = $this->getDistanceFromSourceToAddress->execute(
                    $source,
                    $address
                );
                $sortSources[] = $source;
            } catch (LocalizedException $e) {
                $sourcesWithoutDistance[] = $source;
            }
        }

        // Sort sources by distance
        uasort(
            $sortSources,
            function (SourceInterface $a, SourceInterface $b) use ($distanceBySourceCode) {
                $distanceFromA = $distanceBySourceCode[$a->getSourceCode()];
                $distanceFromB = $distanceBySourceCode[$b->getSourceCode()];

                return ($distanceFromA < $distanceFromB) ? -1 : 1;
            }
        );

        return array_merge($sortSources, $sourcesWithoutDistance);
    }
}
