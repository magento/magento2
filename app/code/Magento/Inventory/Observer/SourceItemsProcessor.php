<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;

/**
 * At the time of processing Product save form this class used to save source items correctly
 * Perform replace strategy of sources for the product
 */
class SourceItemsProcessor
{
    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param SourceItemsSaveInterface $sourceItemsSave
     */
    public function __construct(
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceItemInterfaceFactory $sourceItemFactory,
        DataObjectHelper $dataObjectHelper,
        SourceItemsSaveInterface $sourceItemsSave
    ) {
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sourceItemsSave = $sourceItemsSave;
    }

    /**
     * @param string $sku
     * @param array $sourceItemsData
     * @return void
     * @throws InputException
     */
    public function process(string $sku, array $sourceItemsData)
    {
        $sourceItemsForDelete = $this->getCurrentSourceItemsMap($sku);
        $sourceItemsForSave = [];

        foreach ($sourceItemsData as $sourceItemData) {
            $this->validateSourceItemData($sourceItemData);

            $sourceId = $sourceItemData[SourceItemInterface::SOURCE_ID];
            if (isset($sourceItemsForDelete[$sourceId])) {
                $sourceItem = $sourceItemsForDelete[$sourceId];
            } else {
                /** @var SourceItemInterface $sourceItem */
                $sourceItem = $this->sourceItemFactory->create();
            }

            $sourceItemData[SourceItemInterface::SKU] = $sku;
            $this->dataObjectHelper->populateWithArray($sourceItem, $sourceItemData, SourceItemInterface::class);

            $sourceItemsForSave[] = $sourceItem;
            unset($sourceItemsForDelete[$sourceId]);
        }
        if ($sourceItemsForSave) {
            $this->sourceItemsSave->execute($sourceItemsForSave);
        }
        if ($sourceItemsForDelete) {
            $this->deleteSourceItems($sourceItemsForDelete);
        }
    }

    /**
     * Key is source id, value is Source Item
     *
     * @param string $sku
     * @return SourceItemInterface[]
     */
    private function getCurrentSourceItemsMap(string $sku): array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter(ProductInterface::SKU, $sku)
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        $sourceItemMap = [];
        if ($sourceItems) {
            foreach ($sourceItems as $sourceItem) {
                $sourceItemMap[$sourceItem->getSourceId()] = $sourceItem;
            }
        }
        return $sourceItemMap;
    }

    /**
     * @param array $sourceItemData
     * @return void
     * @throws InputException
     */
    private function validateSourceItemData(array $sourceItemData)
    {
        if (!isset($sourceItemData[SourceItemInterface::SOURCE_ID])) {
            throw new InputException(__('Wrong Product to Source relation parameters given.'));
        }
    }

    /**
     * @param SourceItemInterface[] $sourceItems
     * @return void
     */
    private function deleteSourceItems(array $sourceItems)
    {
        foreach ($sourceItems as $sourceItem) {
            $this->sourceItemRepository->delete($sourceItem);
        }
    }
}
