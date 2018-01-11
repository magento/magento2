<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface;

/**
 * @inheritdoc
 */
class SourceItemFinder implements SourceItemFinderInterface
{
    /**
     * @var SourceItemRepository
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param SourceItemRepository $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SourceItemRepository $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function findBySku(string $sku): SourceItemSearchResultsInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();

        return $this->sourceItemRepository->getList($searchCriteria);
    }
}
