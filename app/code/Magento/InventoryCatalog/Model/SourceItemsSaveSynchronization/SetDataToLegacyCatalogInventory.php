<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\SourceItemsSaveSynchronization;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockItem;
use Magento\InventoryCatalogApi\Model\SourceItemsSaveSynchronizationInterface;

/**
 * Set Qty and status for legacy CatalogInventory Stock Item table
 */
class SetDataToLegacyCatalogInventory
{
    /**
     * @var SetDataToLegacyStockItem
     */
    private $setDataToLegacyStockItem;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $legacyStockItemCriteriaFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    private $legacyStockItemRepository;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @var StockStateProviderInterface
     */
    private $stockStateProvider;

    /**
     * @var Processor
     */
    private $indexerProcessor;

    /**
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem
     * @param StockItemCriteriaInterfaceFactory $legacyStockItemCriteriaFactory
     * @param StockItemRepositoryInterface $legacyStockItemRepository
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param StockStateProviderInterface $stockStateProvider
     * @param Processor $indexerProcessor
     */
    public function __construct(
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        StockItemCriteriaInterfaceFactory $legacyStockItemCriteriaFactory,
        StockItemRepositoryInterface $legacyStockItemRepository,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        StockStateProviderInterface $stockStateProvider,
        Processor $indexerProcessor
    ) {
        $this->setDataToLegacyStockItem = $setDataToLegacyStockItem;
        $this->legacyStockItemCriteriaFactory = $legacyStockItemCriteriaFactory;
        $this->legacyStockItemRepository = $legacyStockItemRepository;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->stockStateProvider = $stockStateProvider;
        $this->indexerProcessor = $indexerProcessor;
    }

    /**
     * @param array $sourceItems
     * @return void
     */
    public function execute(array $sourceItems): void
    {
        $productIds = [];
        foreach ($sourceItems as $sourceItem) {
            $sku = $sourceItem->getSku();

            try {
                $productId = (int)$this->getProductIdsBySkus->execute([$sku])[$sku];
            } catch (NoSuchEntityException $e) {
                // Skip synchronization of for not existed product
                continue;
            }

            $legacyStockItem = $this->getLegacyStockItem($productId);
            if (null === $legacyStockItem) {
                continue;
            }

            $isInStock = (int)$sourceItem->getStatus();

            if ($legacyStockItem->getManageStock()) {
                $legacyStockItem->setIsInStock($isInStock);
                $legacyStockItem->setQty((float)$sourceItem->getQuantity());

                if (false === $this->stockStateProvider->verifyStock($legacyStockItem)) {
                    $isInStock = 0;
                }
            }

            $this->setDataToLegacyStockItem->execute(
                (string)$sourceItem->getSku(),
                (float)$sourceItem->getQuantity(),
                $isInStock
            );
            $productIds[] = $productId;
        }

        if ($productIds) {
            $this->indexerProcessor->reindexList($productIds);
        }
    }

    /**
     * @param int $productId
     * @return null|StockItemInterface
     */
    private function getLegacyStockItem(int $productId): ?StockItemInterface
    {
        $searchCriteria = $this->legacyStockItemCriteriaFactory->create();

        $searchCriteria->addFilter(StockItemInterface::PRODUCT_ID, StockItemInterface::PRODUCT_ID, $productId);
        $searchCriteria->addFilter(StockItemInterface::STOCK_ID, StockItemInterface::STOCK_ID, Stock::DEFAULT_STOCK_ID);

        $stockItemCollection = $this->legacyStockItemRepository->getList($searchCriteria);
        if ($stockItemCollection->getTotalCount() === 0) {
            return null;
        }

        $stockItems = $stockItemCollection->getItems();
        $stockItem = reset($stockItems);
        return $stockItem;
    }
}
