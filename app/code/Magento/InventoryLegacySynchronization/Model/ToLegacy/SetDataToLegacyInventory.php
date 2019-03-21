<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model\ToLegacy;

use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkus;
use Magento\InventoryLegacySynchronization\Model\GetLegacyStockItemsByProductIds;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockItem;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

class SetDataToLegacyInventory
{
    /**
     * @var SetDataToLegacyStockItem
     */
    private $setDataToLegacyStockItem;

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
    private $getProductIdsBySkus;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * SetDataToLegacyCatalogInventory constructor.
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem
     * @param GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds
     * @param StockStateProviderInterface $stockStateProvider
     * @param Processor $indexerProcessor
     * @param GetProductIdsBySkus $getProductIdsBySkus
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds,
        StockStateProviderInterface $stockStateProvider,
        Processor $indexerProcessor,
        GetProductIdsBySkus $getProductIdsBySkus
    ) {
        $this->setDataToLegacyStockItem = $setDataToLegacyStockItem;
        $this->getLegacyStockItemsByProductIds = $getLegacyStockItemsByProductIds;
        $this->stockStateProvider = $stockStateProvider;
        $this->indexerProcessor = $indexerProcessor;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * Synchronously execute legacy alignment
     *
     * @param array $sourceItemsData
     * @throws LocalizedException
     */
    public function execute(array $sourceItemsData): void
    {
        $productIds = [];
        foreach ($sourceItemsData as $sourceItemData) {
            $sku = (string) $sourceItemData[SourceItemInterface::SKU];

            if ($sourceItemData[SourceItemInterface::SOURCE_CODE] !== $this->defaultSourceProvider->getCode()) {
                throw new LocalizedException(__('Only default source can synchronize legacy stock'));
            }

            try {
                $productId = (int) current($this->getProductIdsBySkus->execute([$sku]));
            } catch (NoSuchEntityException $e) {
                continue;
            }

            $legacyStockItems = $this->getLegacyStockItemsByProductIds->execute([$productId]);
            if (!isset($legacyStockItems[$productId])) {
                continue;
            }

            $legacyStockItem = $legacyStockItems[$productId];
            $isInStock = (int) $sourceItemData[SourceItemInterface::STATUS];
            $quantity = (float) $sourceItemData[SourceItemInterface::QUANTITY];

            if ($legacyStockItem->getManageStock()) {
                $legacyStockItem->setIsInStock($isInStock);
                $legacyStockItem->setQty($quantity);

                if (false === $this->stockStateProvider->verifyStock($legacyStockItem)) {
                    $isInStock = 0;
                }
            }

            $this->setDataToLegacyStockItem->execute(
                $sku,
                $quantity,
                $isInStock
            );

            $productIds[] = $productId;
        }

        if (!empty($productIds)) {
            $this->indexerProcessor->reindexList($productIds);
        }
    }
}
