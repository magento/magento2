<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Item;
use Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class StockItemImporter implements StockItemImporterInterface
{
    /**
     * Stock Item Resource Factory
     *
     * @var ItemFactory $stockResourceItemFactory
     */
    private $stockResourceItemFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * StockItemImporter constructor
     *
     * @param ItemFactory $stockResourceItemFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ItemFactory $stockResourceItemFactory,
        LoggerInterface $logger
    ) {
        $this->stockResourceItemFactory = $stockResourceItemFactory;
        $this->logger = $logger;
    }

    /**
     * Handle Import of Stock Item Data
     *
     * @param array $stockData
     * @return void
     * @throws CouldNotSaveException
     */
    public function import(array $stockData)
    {
        /** @var $stockItemResource Item */
        $stockItemResource = $this->stockResourceItemFactory->create();
        $entityTable = $stockItemResource->getMainTable();
        try {
            $stockItemResource->getConnection()->insertOnDuplicate($entityTable, array_values($stockData));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Invalid Stock data for insert'), $e);
        }
    }
}
