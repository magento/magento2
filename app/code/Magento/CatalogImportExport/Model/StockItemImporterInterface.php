<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model;

/**
 * Interface StockItemImporterInterface
 *
 * @api
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
     */
    public function import(array $stockData);
}
