<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model;

use Magento\CatalogImportExport\Model\StockItemImporterInterface;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Inventory\Model\SourceItemFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;

class StockItemImporter implements StockItemImporterInterface
{
    /**
     * Source Items Save Interface for saving multiple source items
     *
     * @var SourceItemsSaveInterface $sourceItemsSave
     */
    private $sourceItemsSave;

    /**
     * Source Item Interface Factory
     *
     * @var SourceItemFactory $sourceItemFactory
     */
    private $sourceItemFactory;

    /**
     * Default Source Provider
     *
     * @var DefaultSourceProviderInterface $defaultSource
     */
    private $defaultSource;

    /**
     * StockItemImporter constructor
     *
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param SourceItemFactory $sourceItemFactory
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemFactory $sourceItemFactory,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->defaultSource = $defaultSourceProvider;
    }

    /**
     * Handle Import of Stock Item Data
     *
     * @param array $stockData
     * @return void
     */
    public function import(array $stockData): void
    {
        $sourceItems = [];
        foreach ($stockData as $stockDatum) {
            if (isset($stockDatum[Product::COL_SKU])) {
                $sku = $stockDatum[Product::COL_SKU];
                $sourceItem = $this->sourceItemFactory->create();
                $sourceItem->setSku($sku);
                $sourceItem->setSourceId($this->defaultSource->getId());
                $sourceItem->setQuantity($stockDatum['qty']);
                $sourceItem->setStatus($stockDatum['is_in_stock']);
                $sourceItems[] = $sourceItem;
            }
        }
        if (count($sourceItems) > 0) {
            /** Magento\Inventory\Model\SourceItem[] $sourceItems */
            $this->sourceItemsSave->execute($sourceItems);
        }
    }
}
