<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Model;

use Magento\Framework\Validation\ValidationResult;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferInterface;

interface PartialInventoryTransferValidatorInterface
{
    /**
     * Validates a partial transfer request.
     *
     * @param PartialInventoryTransferInterface $transfer
     * @return ValidationResult
     */
    public function validate(PartialInventoryTransferInterface $transfer): ValidationResult;
}