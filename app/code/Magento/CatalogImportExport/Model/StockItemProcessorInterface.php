<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Validation\ValidationException;

interface StockItemProcessorInterface
{
    /**
     * Handle Import of Stock Item Data
     *
     * @param array $stockData
     * @param array $importedData
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws ValidationException
     */
    public function process(array $stockData, array $importedData): void;
}
