<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\InputException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
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
     * @var SourceItemsDeleteInterface
     */
    private $sourceItemsDelete;

    /**
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param SourceItemsDeleteInterface $sourceItemsDelete
     */
    public function __construct(
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceItemInterfaceFactory $sourceItemFactory,
        DataObjectHelper $dataObjectHelper,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemsDeleteInterface $sourceItemsDelete
    ) {
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemsDelete = $sourceItemsDelete;
    }

    /**
     * @param string $sku
     * @param array $sourceItemsData
     * @return void
     * @throws InputException
     */
    public function process($sku, array $sourceItemsData)
    {
        $sourceItemsForDelete = $this->getCurrentSourceItemsMap($sku);
        $sourceItemsForSave = [];

        foreach ($sourceItemsData as $sourceItemData) {
            $this->validateSourceItemData($sourceItemData);

            $sourceCode = $sourceItemData[SourceItemInterface::SOURCE_CODE];
            if (isset($sourceItemsForDelete[$sourceCode])) {
                $sourceItem = $sourceItemsForDelete[$sourceCode];
            } else {
                /** @var SourceItemInterface $sourceItem */
                $sourceItem = $this->sourceItemFactory->create();
            }

            $sourceItemData[SourceItemInterface::SKU] = $sku;
            $this->dataObjectHelper->populateWithArray($sourceItem, $sourceItemData, SourceItemInterface::class);

            $sourceItemsForSave[] = $sourceItem;
            unset($sourceItemsForDelete[$sourceCode]);
        }
        if ($sourceItemsForSave) {
            $this->sourceItemsSave->execute($sourceItemsForSave);
        }
        if ($sourceItemsForDelete) {
            $this->sourceItemsDelete->execute($sourceItemsForDelete);
        }
    }

    /**
     * Get Source Items Hash Table by SKU
     *
     * @param string $sku
     * @return SourceItemInterface[]
     */
    private function getCurrentSourceItemsMap(string $sku): array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->addFilter(ProductInterface::SKU, $sku)->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        $sourceItemMap = [];
        if ($sourceItems) {
            foreach ($sourceItems as $sourceItem) {
                $sourceItemMap[$sourceItem->getSourceCode()] = $sourceItem;
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
        if (!isset($sourceItemData[SourceItemInterface::SOURCE_CODE])) {
            throw new InputException(__('Wrong Product to Source relation parameters given.'));
        }
    }
}
