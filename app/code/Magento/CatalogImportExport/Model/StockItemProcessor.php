<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model;

class StockItemProcessor implements StockItemProcessorInterface
{
    /**
     * @var StockItemImporterInterface
     */
    private $stockItemImporter;

    /**
     * @param StockItemImporterInterface $stockItemImporter
     */
    public function __construct(
        StockItemImporterInterface $stockItemImporter
    ) {
        $this->stockItemImporter = $stockItemImporter;
    }

    /**
     * @inheritdoc
     */
    public function process(array $stockData, array $importedData): void
    {
        $this->stockItemImporter->import($stockData);
    }
}
