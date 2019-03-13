<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\SourceItemsSaveSynchronization;

use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\Synchronize;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockItem;

/**
 * Set Qty and status for legacy CatalogInventory Stock Item table
 * @deprecated
 * @see \Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\Synchronize
 */
class SetDataToLegacyCatalogInventory
{
    /**
     * @var Synchronize
     */
    private $synchronize;

    /**
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem @deprecated
     * @param StockItemCriteriaInterfaceFactory $legacyStockItemCriteriaFactory @deprecated
     * @param StockItemRepositoryInterface $legacyStockItemRepository @deprecated
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus @deprecated
     * @param StockStateProviderInterface $stockStateProvider @deprecated
     * @param Processor $indexerProcessor @deprecated
     * @param Synchronize $synchronize
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        StockItemCriteriaInterfaceFactory $legacyStockItemCriteriaFactory,
        StockItemRepositoryInterface $legacyStockItemRepository,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        StockStateProviderInterface $stockStateProvider,
        Processor $indexerProcessor,
        Synchronize $synchronize = null
    ) {
        $this->alignLegacyCatalogInventoryByProducts = $synchronize ?:
            ObjectManager::getInstance()->get(Synchronize::class);
    }

    /**
     * @param array $sourceItems
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $sourceItems): void
    {
        $skus = [];
        foreach ($sourceItems as $sourceItem) {
            $skus[] = $sourceItem->getSku();
        }

        $this->synchronize->execute(Synchronize::DIRECTION_TO_LEGACY, $skus);
    }
}
