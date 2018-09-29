<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Model;

use Magento\Framework\Validation\ValidationResult;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Responsible for Source item validation
 * Extension point for base validation
 *
 * @api
 */
interface SourceItemValidatorInterface
{
    /**
     * @param SourceItemInterface $sourceItem
     * @return ValidationResult
     */
    public function validate(SourceItemInterface $sourceItem): ValidationResult;
}
