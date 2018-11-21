<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Plugin\Import;

use Magento\CatalogImportExport\Model\StockItemImporterInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

class SourceItemImporter
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
     * @var SourceItemInterfaceFactory $sourceItemFactory
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
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->defaultSource = $defaultSourceProvider;
    }

    /**
     * After plugin Import to import Stock Data to Source Items
     *
     * @param StockItemImporterInterface $subject
     * @param null $result
     * @param array $stockData
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     * @return void
     * @see StockItemImporterInterface::import()
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterImport(
        StockItemImporterInterface $subject,
        $result,
        array $stockData
    ) {
        $sourceItems = [];
        foreach ($stockData as $sku => $stockDatum) {
            $inStock = (isset($stockDatum['is_in_stock'])) ? intval($stockDatum['is_in_stock']) : 0;
            $qty = (isset($stockDatum['qty'])) ? $stockDatum['qty'] : 0;
            /** @var SourceItemInterface $sourceItem */
            $sourceItem = $this->sourceItemFactory->create();
            $sourceItem->setSku((string)$sku);
            $sourceItem->setSourceCode($this->defaultSource->getCode());
            $sourceItem->setQuantity((float)$qty);
            $sourceItem->setStatus($inStock);
            $sourceItems[] = $sourceItem;
        }
        if (count($sourceItems) > 0) {
            /** SourceItemInterface[] $sourceItems */
            $this->sourceItemsSave->execute($sourceItems);
        }
    }
}
