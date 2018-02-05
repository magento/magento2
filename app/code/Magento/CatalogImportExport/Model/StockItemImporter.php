<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
use Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;

class StockItemImporter implements StockItemImporterInterface
{
    /**
     * Stock Item Resource Factory
     *
     * @var ItemFactory $stockResourceItemFactory
     */
    private $stockResourceItemFactory;

    /**
     * StockItemImporter constructor
     *
     * @param ItemFactory $stockResourceItemFactory
     */
    public function __construct(
        ItemFactory $stockResourceItemFactory
    ) {
        $this->stockResourceItemFactory = $stockResourceItemFactory;
    }

    /**
     * Handle Import of Stock Item Data
     *
     * @param array $stockData
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function import(array $stockData)
    {
        /** @var $stockItemResource Item */
        $stockItemResource = $this->stockResourceItemFactory->create();
        $entityTable = $stockItemResource->getMainTable();
        $stockItemResource->getConnection()->insertOnDuplicate($entityTable, array_values($stockData));
    }
}
