<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Exception\LocalizedException;

/**
 * Get a list of legacy stock items by products ids
 */
class GetLegacyStockItemsByProductIds
{
    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * GetLegacyStockItemsByProductIds constructor.
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        StockItemRepositoryInterface $stockItemRepository,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
    }

    /**
     * @param array $productIds
     * @return StockItemInterface[]
     * @throws LocalizedException
     */
    public function execute(array $productIds): array
    {
        $searchCriteria = $this->stockItemCriteriaFactory->create();
        $searchCriteria->setProductsFilter($productIds);
        $searchCriteria->addFilter(
            StockItemInterface::STOCK_ID,
            StockItemInterface::STOCK_ID,
            Stock::DEFAULT_STOCK_ID
        );

        $stockItems = $this->stockItemRepository->getList($searchCriteria)->getItems();
        $productIdsIndex = [];
        foreach ($stockItems as $stockItem) {
            $productIdsIndex[] = (int) $stockItem->getProductId();
        }

        return array_combine($productIdsIndex, $stockItems);
    }
}
