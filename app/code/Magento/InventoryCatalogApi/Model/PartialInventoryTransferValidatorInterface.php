<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Model;

use Magento\Framework\Validation\ValidationResult;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferItemInterface;

/**
 * Validator for Partial Inventory transfer API.
 *
 * @api
 */
interface PartialInventoryTransferValidatorInterface
{
    /**
     * Validates a partial transfer request.
     *
     * @param string $originSourceCode
     * @param string $destinationSourceCode
     * @param PartialInventoryTransferItemInterface[] $items
     * @return ValidationResult
     */
    public function validate(string $originSourceCode, string $destinationSourceCode, array $items): ValidationResult;
}
