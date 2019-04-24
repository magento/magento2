<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;

/**
 * Retrieve source items for a defined set of skus and sorted source codes
 *
 * Useful for determining presence in stock and source selection
 *
 * @api
 */
class GetInStockSourceItemsBySkusAndSortedSource
{
    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param array $skus
     * @param array $sortedSourceCodes
     * @return SourceItemInterface[]
     */
    public function execute(array $skus, array $sortedSourceCodes): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $skus, 'in')
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sortedSourceCodes, 'in')
            ->addFilter(SourceItemInterface::STATUS, SourceItemInterface::STATUS_IN_STOCK)
            ->create();

        $items = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        $itemsSorting = [];
        foreach ($items as $item) {
            $itemsSorting[] = array_search($item->getSourceCode(), $sortedSourceCodes, true);
        }

        array_multisort($itemsSorting, SORT_NUMERIC, SORT_ASC, $items);
        return $items;
    }
}
