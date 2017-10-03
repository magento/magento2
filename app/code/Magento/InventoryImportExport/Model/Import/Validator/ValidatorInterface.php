<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Import\Validator;

use Magento\Framework\Validation\ValidationResult;

/**
 * Extension point for row validation (Service Provider Interface - SPI)
 *
 * @api
 */
interface ValidatorInterface
{
    /**
     * @param array $rowData
     * @param int $rowNumber
     * @return ValidationResult
     */
    public function validate(array $rowData, $rowNumber);
}
