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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\AlignLegacyCatalogInventoryByProducts;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockItem;

/**
 * Set Qty and status for legacy CatalogInventory Stock Item table
 * @deprecated
 * @see \Magento\InventoryCatalog\Model\SourceItemsSaveSynchronization\SetDataToLegacyCatalogInventory
 */
class SetDataToLegacyCatalogInventory
{
    /**
     * @var AlignLegacyCatalogInventoryByProducts
     */
    private $alignLegacyCatalogInventoryByProducts;

    /**
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem
     * @param StockItemCriteriaInterfaceFactory $legacyStockItemCriteriaFactory
     * @param StockItemRepositoryInterface $legacyStockItemRepository
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param StockStateProviderInterface $stockStateProvider
     * @param Processor $indexerProcessor
     * @param AlignLegacyCatalogInventoryByProducts $alignLegacyCatalogInventoryByProducts
     * @SupressWarnings(PHPMD.UnusedFormalParameter)
     * @SupressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        StockItemCriteriaInterfaceFactory $legacyStockItemCriteriaFactory,
        StockItemRepositoryInterface $legacyStockItemRepository,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        StockStateProviderInterface $stockStateProvider,
        Processor $indexerProcessor,
        AlignLegacyCatalogInventoryByProducts $alignLegacyCatalogInventoryByProducts = null
    ) {
        $this->alignLegacyCatalogInventoryByProducts = $alignLegacyCatalogInventoryByProducts ?:
            ObjectManager::getInstance()->get(AlignLegacyCatalogInventoryByProducts::class);
    }

    /**
     * @param array $sourceItems
     * @return void
     */
    public function execute(array $sourceItems): void
    {
        $skus = [];
        foreach ($sourceItems as $sourceItem) {
            $skus[] = $sourceItem->getSku();
        }

        $this->alignLegacyCatalogInventoryByProducts->execute($skus);
    }
}
