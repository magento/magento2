<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model;

/**
 * Interface StockItemImporterInterface
 *
 * @api
 * @since 101.0.0
 */
interface StockItemImporterInterface
{
    /**
     * Handle Import of Stock Item Data
     *
     * @param array $stockData
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     * @since 101.0.0
     */
    public function import(array $stockData);
}
