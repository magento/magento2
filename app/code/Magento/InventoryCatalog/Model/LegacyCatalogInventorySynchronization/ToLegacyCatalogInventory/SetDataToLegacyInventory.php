<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\ToLegacyCatalogInventory;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryCatalog\Model\GetDefaultSourceItemsBySkus;
use Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\GetLegacyStockItemsByProductIds;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockItem;

class SetDataToLegacyInventory
{
    /**
     * @var SetDataToLegacyStockItem
     */
    private $setDataToLegacyStockItem;

    /**
     * @var Product
     */
    private $productResourceModel;

    /**
     * @var GetDefaultSourceItemsBySkus
     */
    private $getDefaultSourceItemsBySkus;

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
     * SetDataToLegacyCatalogInventory constructor.
     * @param GetDefaultSourceItemsBySkus $getDefaultSourceItemsBySkus
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem
     * @param GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds
     * @param StockStateProviderInterface $stockStateProvider
     * @param Processor $indexerProcessor
     * @param Product $productResourceModel
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetDefaultSourceItemsBySkus $getDefaultSourceItemsBySkus,
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds,
        StockStateProviderInterface $stockStateProvider,
        Processor $indexerProcessor,
        Product $productResourceModel
    ) {
        $this->setDataToLegacyStockItem = $setDataToLegacyStockItem;
        $this->productResourceModel = $productResourceModel;
        $this->getDefaultSourceItemsBySkus = $getDefaultSourceItemsBySkus;
        $this->getLegacyStockItemsByProductIds = $getLegacyStockItemsByProductIds;
        $this->stockStateProvider = $stockStateProvider;
        $this->indexerProcessor = $indexerProcessor;
    }

    /**
     * Synchronously execute legacy alignment
     *
     * @param array $skus
     * @throws LocalizedException
     */
    public function execute(array $skus): void
    {
        $sourceItems = $this->getDefaultSourceItemsBySkus->execute($skus);
        $productIds = $this->productResourceModel->getProductsIdsBySkus($skus);
        $legacyStockItems = $this->getLegacyStockItemsByProductIds->execute($productIds);

        foreach ($sourceItems as $sourceItem) {
            $sku = $sourceItem->getSku();

            if (!isset($productIds[$sku])) {
                continue; // This product does not exist anymore
            }

            $productId = (int) $productIds[$sku];

            if (!isset($legacyStockItems[$productId])) {
                continue;
            }

            $legacyStockItem = $legacyStockItems[$productId];
            $isInStock = (int) $sourceItem->getStatus();

            if ($legacyStockItem->getManageStock()) {
                $legacyStockItem->setIsInStock($isInStock);
                $legacyStockItem->setQty((float)$sourceItem->getQuantity());

                if (false === $this->stockStateProvider->verifyStock($legacyStockItem)) {
                    $isInStock = 0;
                }
            }

            $this->setDataToLegacyStockItem->execute(
                (string) $sourceItem->getSku(),
                (float) $sourceItem->getQuantity(),
                $isInStock
            );
        }

        if (!empty($productIds)) {
            $this->indexerProcessor->reindexList($productIds);
        }
    }
}
