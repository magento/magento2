<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * @inheritdoc
 */
class IsAnySourceInStockCondition implements IsProductSalableInterface
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
     * IsAnySourceInStockCondition constructor.
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(string $sku, int $stockId): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->addFilter(SourceItemInterface::STATUS, SourceItemInterface::STATUS_IN_STOCK)
            ->create();

        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        return (bool)count($sourceItems);
    }
}
