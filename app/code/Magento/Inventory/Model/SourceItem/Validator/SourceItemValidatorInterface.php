<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\Source\Validator;

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
