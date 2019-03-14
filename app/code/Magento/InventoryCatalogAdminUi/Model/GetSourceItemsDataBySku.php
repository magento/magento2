<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Model;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class GetSourceItemsDataBySku
{
    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceRepositoryInterface $sourceRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param string $sku
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(string $sku): array
    {
        $sourceItemsData = [];

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        $sourcesCache = [];
        foreach ($sourceItems as $sourceItem) {
            $sourceCode = $sourceItem->getSourceCode();
            if (!isset($sourcesCache[$sourceCode])) {
                $sourcesCache[$sourceCode] = $this->sourceRepository->get($sourceCode);
            }

            $source = $sourcesCache[$sourceCode];

            $sourceItemsData[] = [
                SourceItemInterface::SOURCE_CODE => $sourceItem->getSourceCode(),
                SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                SourceItemInterface::STATUS => $sourceItem->getStatus(),
                SourceInterface::NAME => $source->getName(),
                'source_status' => $source->isEnabled(),
            ];
        }

        return $sourceItemsData;
    }
}
