<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model\ToLegacy;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkus;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;
use Magento\InventoryLegacySynchronization\Model\GetLegacyStockItemsByProductIds;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryLegacySynchronization\Model\ResourceModel\UpdateLegacyStockItemsData;

/**
 * Copy source item information to legacy stock
 */
class SetDataToLegacyInventory
{
    /**
     * @var GetLegacyStockItemsByProductIds
     */
    private $getLegacyStockItemsByProductIds;

    /**
     * @var StockStateProviderInterface
     */
    private $stockStateProvider;

    /**
     * @var Processor
     */
    private $indexerProcessor;

    /**
     * @var GetProductIdsBySkus
     */
    private $productResourceModel;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var UpdateLegacyStockItemsData
     */
    private $updateLegacyStockItemsData;

    /**
     * @var SourceItemIndexer
     */
    private $sourceItemIndexer;

    /**
     * @param UpdateLegacyStockItemsData $updateLegacyStockItemsData
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds
     * @param SourceItemIndexer $sourceItemIndexer
     * @param StockStateProviderInterface $stockStateProvider
     * @param Processor $indexerProcessor
     * @param ProductResourceModel $productResourceModel
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        UpdateLegacyStockItemsData $updateLegacyStockItemsData,
        DefaultSourceProviderInterface $defaultSourceProvider,
        GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds,
        SourceItemIndexer $sourceItemIndexer,
        StockStateProviderInterface $stockStateProvider,
        Processor $indexerProcessor,
        ProductResourceModel $productResourceModel
    ) {
        $this->getLegacyStockItemsByProductIds = $getLegacyStockItemsByProductIds;
        $this->stockStateProvider = $stockStateProvider;
        $this->indexerProcessor = $indexerProcessor;
        $this->productResourceModel = $productResourceModel;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->updateLegacyStockItemsData = $updateLegacyStockItemsData;
        $this->sourceItemIndexer = $sourceItemIndexer;
    }

    /**
     * Synchronously execute legacy alignment
     *
     * @param array $sourceItemsData
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function execute(array $sourceItemsData): void
    {
        $productSkus = array_column($sourceItemsData, SourceItemInterface::SKU);
        $productIdsBySku = $this->productResourceModel->getProductsIdsBySkus($productSkus);

        $legacyStockItemsByProductsIds =
            $this->getLegacyStockItemsByProductIds->execute(array_values($productIdsBySku));

        $legacyItemsToUpdateData = [];
        foreach ($sourceItemsData as $sourceItemData) {
            $sku = (string) $sourceItemData[SourceItemInterface::SKU];

            if ($sourceItemData[SourceItemInterface::SOURCE_CODE] !== $this->defaultSourceProvider->getCode()) {
                throw new LocalizedException(__('Only default source can synchronize legacy stock'));
            }

            if (!isset($productIdsBySku[$sku])) {
                continue;
            }

            $productId = $productIdsBySku[$sku];

            if (!isset($legacyStockItemsByProductsIds[$productId])) {
                continue;
            }

            $legacyStockItem = $legacyStockItemsByProductsIds[$productId];
            $isInStock = (int) $sourceItemData[SourceItemInterface::STATUS];
            $quantity = (float) $sourceItemData[SourceItemInterface::QUANTITY];

            if ($legacyStockItem->getManageStock()) {
                $legacyStockItem->setIsInStock($isInStock);
                $legacyStockItem->setQty($quantity);

                if (false === $this->stockStateProvider->verifyStock($legacyStockItem)) {
                    $isInStock = 0;
                }
            }

            $legacyItemsToUpdateData[] = [
                StockItemInterface::QTY => $quantity,
                StockItemInterface::IS_IN_STOCK => $isInStock,
                StockItemInterface::PRODUCT_ID => $productId,
                'stock_id' => 1,
                'website_id' => 0,
            ];

            $productIds[] = $productId;
        }

        if (!empty($legacyItemsToUpdateData)) {
            $this->updateLegacyStockItemsData->execute($legacyItemsToUpdateData);
        }

        if (!empty($productIds)) {
            $this->indexerProcessor->reindexList($productIds);
            $this->sourceItemIndexer->executeList(array_column($sourceItemsData, 'source_item_id'));
        }
    }
}
