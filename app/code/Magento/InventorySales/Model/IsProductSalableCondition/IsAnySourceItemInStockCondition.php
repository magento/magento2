<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForSkuInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Check if product has source items with the in stock status
 */
class IsAnySourceItemInStockCondition implements IsProductSalableInterface
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
     * @var GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    private $getSourcesAssignedToStockOrderedByPriority;

    /**
     * @var IsSourceItemManagementAllowedForSkuInterface
     */
    private $isSourceItemManagementAllowedForSku;

    /**
     * @var ManageStockCondition
     */
    private $manageStockCondition;

    /**
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     * @param IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku
     * @param ManageStockCondition $manageStockCondition
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        IsSourceItemManagementAllowedForSkuInterface $isSourceItemManagementAllowedForSku,
        ManageStockCondition $manageStockCondition
    ) {
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
        $this->isSourceItemManagementAllowedForSku = $isSourceItemManagementAllowedForSku;
        $this->manageStockCondition = $manageStockCondition;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        // TODO Must be removed once MSI-2131 is complete.
        if ($this->manageStockCondition->execute($sku, $stockId)) {
            return true;
        }

        if (!$this->isSourceItemManagementAllowedForSku->execute($sku)) {
            return true;
        }

        $sourceCodes = $this->getSourceCodesAssignedToStock($stockId);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCodes, 'in')
            ->addFilter(SourceItemInterface::STATUS, SourceItemInterface::STATUS_IN_STOCK)
            ->create();

        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        return (bool)count($sourceItems);
    }

    /**
     * Provides source codes for certain stock
     *
     * @param int $stockId
     *
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSourceCodesAssignedToStock(int $stockId): array
    {
        $sources = $this->getSourcesAssignedToStockOrderedByPriority->execute($stockId);
        $sourceCodes = [];
        foreach ($sources as $source) {
            if ($source->isEnabled()) {
                $sourceCodes[] = $source->getSourceCode();
            }
        }

        return $sourceCodes;
    }
}
