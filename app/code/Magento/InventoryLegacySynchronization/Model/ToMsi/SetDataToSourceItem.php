<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model\ToMsi;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;
use Magento\InventoryLegacySynchronization\Model\GetDefaultSourceItemsBySkus;
use Magento\InventoryLegacySynchronization\Model\GetLegacyStockItemsByProductIds;
use Magento\InventoryLegacySynchronization\Model\ResourceModel\UpdateSourceItemsData;

/**
 * Copy legacy stock item information to MSI source items
 */
class SetDataToSourceItem
{
    /**
     * @var UpdateSourceItemsData
     */
    private $updateSourceItemsData;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var GetLegacyStockItemsByProductIds
     */
    private $getLegacyStockItemsByProductIds;

    /**
     * @var SourceItemIndexer
     */
    private $sourceItemIndexer;

    /**
     * @var GetDefaultSourceItemsBySkus
     */
    private $getDefaultSourceItemsBySkus;

    /**
     * @var ProductResourceModel
     */
    private $productResourceModel;

    /**
     * @param UpdateSourceItemsData $updateSourceItemsData
     * @param GetDefaultSourceItemsBySkus $getDefaultSourceItemsBySkus
     * @param GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SourceItemIndexer $sourceItemIndexer
     * @param ProductResourceModel $productResourceModel
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        UpdateSourceItemsData $updateSourceItemsData,
        GetDefaultSourceItemsBySkus $getDefaultSourceItemsBySkus,
        GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SourceItemIndexer $sourceItemIndexer,
        ProductResourceModel $productResourceModel
    ) {
        $this->updateSourceItemsData = $updateSourceItemsData;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->getLegacyStockItemsByProductIds = $getLegacyStockItemsByProductIds;
        $this->sourceItemIndexer = $sourceItemIndexer;
        $this->getDefaultSourceItemsBySkus = $getDefaultSourceItemsBySkus;
        $this->productResourceModel = $productResourceModel;
    }

    /**
     * @param array $legacyItemsData
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function execute(array $legacyItemsData): void
    {
        $sourceItemsData = [];
        $productIds = array_column($legacyItemsData, StockItemInterface::PRODUCT_ID);
        $defaultSourceCode = $this->defaultSourceProvider->getCode();

        $legacyStockItemsByProductsIds =
            $this->getLegacyStockItemsByProductIds->execute(array_values($productIds));

        $productSkus = $this->productResourceModel->getProductsSku($productIds);
        $productSkusById = array_combine(
            array_column($productSkus, 'entity_id'),
            array_column($productSkus, 'sku')
        );

        foreach ($legacyItemsData as $legacyItemData) {
            $productId = (int) $legacyItemData[StockItemInterface::PRODUCT_ID];

            if (!isset($productSkusById[$productId], $legacyStockItemsByProductsIds[$productId])) {
                continue;
            }

            $productSku = $productSkusById[$productId];

            /** @var StockItemInterface $legacyStockItem */
            $legacyStockItem = $legacyStockItemsByProductsIds[$productId];

            $sourceItemsData[] = [
                SourceItemInterface::SOURCE_CODE => $defaultSourceCode,
                SourceItemInterface::SKU => $productSku,
                SourceItemInterface::QUANTITY => (float) $legacyStockItem->getQty(),
                SourceItemInterface::STATUS => (int) $legacyStockItem->getIsInStock(),
            ];
        }

        if (!empty($sourceItemsData)) {
            $this->updateSourceItemsData->execute($sourceItemsData);

            $sourceItemsToReindex = $this->getDefaultSourceItemsBySkus->execute(array_values($productSkusById));
            $sourceItemsIdsToReindex = [];
            foreach ($sourceItemsToReindex as $sourceItemToReindex) {
                $sourceItemsIdsToReindex[] = (int)$sourceItemToReindex->getId();
            }

            $this->sourceItemIndexer->executeList($sourceItemsIdsToReindex);
        }
    }
}
