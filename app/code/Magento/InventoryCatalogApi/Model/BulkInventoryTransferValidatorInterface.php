<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Model;

use Magento\Framework\Validation\ValidationResult;

/**
 * Responsible for Product Source un-assignment validation
 *
 * @api
 */
interface BulkInventoryTransferValidatorInterface
{
    /**
     * Validates a mass un-assignment request
     *
     * @param string[] $skus
     * @param string $originSource
     * @param string $destinationSource
     * @return ValidationResult
     */
    public function validate(array $skus, string $originSource, string $destinationSource): ValidationResult;
}
